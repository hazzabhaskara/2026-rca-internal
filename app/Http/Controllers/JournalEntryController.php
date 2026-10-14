<?php

namespace App\Http\Controllers;

use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Project;
use App\Setting;
use App\StartNewYear;
use App\Services\StartNewYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JournalEntryController extends Controller
{
    //

    public function index()
    {
        $tahunList = JournalEntry::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $query = JournalEntry::orderBy('tanggal');
        if ($bulan = request('filter_bulan')) {
            $query->whereMonth('tanggal', $bulan);
        }

        if ($tahun = request('filter_tahun')) {
            $query->whereYear('tanggal', $tahun);
        }

        $searchable = ['source', 'comment', 'tanggal'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }


        // Eksekusi query dengan pagination
        $data = $query->paginate(15)->appends(request()->query());

        return view('journal_entry.index', compact('data', 'tahunList'));
    }
    public function create()
    {
        $departemenAkun = DepartemenAkun::all();
        $periodeBuku = StartNewYear::all();
        $project = Project::all();
        $fiscalJournalMode = Setting::get('fiscal_journal_mode', 'disabled');
        return view('journal_entry.create', compact('departemenAkun', 'periodeBuku', 'project', 'fiscalJournalMode'));
    }
    public function getAutoData(Request $request)
    {
        $tanggal = $request->get('tanggal');
        $kodeAkun = $request->get('kode_akun');

        $periode = DB::table('start_new_years')->where('status', 'Opening')->first();

        // Kalau periode aktif tidak ada
        if (!$periode || !$tanggal) {
            return response()->json(['success' => false]);
        }

        $tahun = $periode->tahun;

        // Kalau tanggal bukan akhir periode → langsung return tanpa entries
        if ($tanggal != $periode->akhir_periode) {
            return response()->json(['success' => false]);
        }

        // Query dasar
        $query = DB::table('journal_entry_details as d')
            ->join('journal_entries as j', 'd.journal_entry_id', '=', 'j.id')
            ->whereYear('j.tanggal', $tahun);

        if ($kodeAkun) {
            $result = $query
                ->where('d.kode_akun', $kodeAkun)
                ->select(
                    'd.kode_akun',
                    DB::raw('SUM(d.debits) as total_debit'),
                    DB::raw('SUM(d.credits) as total_credit')
                )
                ->groupBy('d.kode_akun')
                ->first();

            if (!$result) {
                return response()->json([
                    'success' => true,
                    'total_debit' => 0,
                    'total_credit' => 0
                ]);
            }

            $tipeAkun = DB::table('chart_of_accounts')
                ->where('kode_akun', $kodeAkun)
                ->value('tipe_akun');

            if (in_array($tipeAkun, ['Pendapatan', 'Beban'])) {
                $debitAkhir = $result->total_credit ?? 0;
                $creditAkhir = $result->total_debit ?? 0;
            } else {
                $debitAkhir = $result->total_debit ?? 0;
                $creditAkhir = $result->total_credit ?? 0;
            }

            return response()->json([
                'success' => true,
                'total_debit' => $debitAkhir,
                'total_credit' => $creditAkhir
            ]);
        }

        // Ambil hanya akun Pendapatan dan Beban
        $entries = $query
            ->join('chart_of_accounts as coa', 'd.kode_akun', '=', 'coa.kode_akun')
            ->whereIn('coa.tipe_akun', ['Pendapatan', 'Beban']) // 🔹 filter di sini
            ->select(
                'd.kode_akun',
                'coa.nama_akun',
                'coa.tipe_akun',
                DB::raw('SUM(d.debits) as total_debit'),
                DB::raw('SUM(d.credits) as total_credit')
            )
            ->groupBy('d.kode_akun', 'coa.nama_akun', 'coa.tipe_akun')
            ->get();


        // Balikkan untuk Pendapatan dan Beban
        $entries = $entries->map(function ($item) {
            $tipeAkun = DB::table('chart_of_accounts')
                ->where('kode_akun', $item->kode_akun)
                ->value('tipe_akun');

            if (in_array($tipeAkun, ['Pendapatan', 'Beban'])) {
                $tmp = $item->total_debit;
                $item->total_debit = $item->total_credit;
                $item->total_credit = $tmp;
            }
            return $item;
        });

        $totalDebit = $entries->sum('total_debit');
        $totalCredit = $entries->sum('total_credit');

        if ($totalDebit > $totalCredit) {
            $debitAkhir = 0;
            $creditAkhir = $totalDebit - $totalCredit;
        } elseif ($totalCredit > $totalDebit) {
            $debitAkhir = $totalCredit - $totalDebit;
            $creditAkhir = 0;
        } else {
            $debitAkhir = 0;
            $creditAkhir = 0;
        }

        $akunLaba = DB::table('chart_of_accounts')->where('level_akun', 'X')->first();

        if ($akunLaba) {
            $entries->push((object)[
                'kode_akun' => $akunLaba->kode_akun,
                'nama_akun' => $akunLaba->nama_akun,
                'total_debit' => $debitAkhir,
                'total_credit' => $creditAkhir
            ]);
        }

        return response()->json([
            'success' => true,
            'entries' => $entries
        ]);
    }


    public function store(Request $request)
    {
        // Bersihkan dan filter input
        $cleanedItems = collect($request->items)
            ->filter(fn($item) => !empty($item['kode_akun']))
            ->map(function ($item) {
                $item['debits']  = $this->normalizeNumber($item['debits'] ?? null);
                $item['credits'] = $this->normalizeNumber($item['credits'] ?? null);
                return $item;
            })
            ->values()
            ->all();
        // dump('RAW items:', $request->items);
        // dump('CLEANED items:', $cleanedItems);

        // Gabungkan kembali ke dalam request
        $request->merge(['items' => $cleanedItems]);

        // Validasi
        $request->validate([
            'source' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'comment' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_akun' => 'required|string',
            'items.*.departemen_akun_id' => 'nullable|exists:departemen_akuns,id',
            'items.*.debits' => 'nullable|numeric',
            'items.*.credits' => 'nullable|numeric',
            'items.*.comment' => 'nullable|string',
            'items.*.project_id' => 'nullable|exists:projects,id',
            'items.*.pajak' => 'nullable|boolean',
            'items.*.penyesuaian_fiskal' => 'nullable|string',
            'items.*.kode_fiscal' => 'nullable|string',
        ]);

        // Validasi backyear: hanya boleh 1 tahun ke belakang (Admin = unlimited)
        $isAdmin = auth()->check() && auth()->user()->hasRole('Admin');
        if (!$isAdmin) {
            $periodeAktif = DB::table('start_new_years')->where('status', 'Opening')->first();
            $tahunAktif = $periodeAktif ? $periodeAktif->tahun : (int) date('Y');
            $minYear = $tahunAktif - 1; // Backyear 1 tahun

            $tahunInput = (int) date('Y', strtotime($request->tanggal));
            if ($tahunInput < $minYear) {
                return back()->withInput()->withErrors([
                    'tanggal' => "Transaksi hanya bisa diinput untuk tahun {$minYear} atau lebih baru (backyear maksimal 1 tahun)."
                ]);
            }
        }

        // Hitung total debit & kredit
        $totalDebit = collect($cleanedItems)->sum(fn($i) => (float) $i['debits']);
        $totalKredit = collect($cleanedItems)->sum(fn($i) => (float) $i['credits']);

        // dump('TOTAL DEBIT:', $totalDebit, 'TOTAL CREDUT:', $totalKredit);

        // Gunakan toleransi kecil agar float rounding tidak bikin gagal
        if (abs($totalDebit - $totalKredit) > 0.001) {
            return back()->withInput()->with(
                'error',
                'Total Debit (' . number_format($totalDebit, 2, ',', '.') .
                    ') dan Total Kredit (' . number_format($totalKredit, 2, ',', '.') .
                    ') harus sama!'
            );
        }

        // Cegah hanya sisi debit atau kredit
        if (($totalDebit == 0 && $totalKredit > 0) || ($totalKredit == 0 && $totalDebit > 0)) {
            return back()->withInput()->withErrors([
                'items' => 'Harus ada lawan transaksi, tidak boleh hanya debit atau hanya kredit.'
            ]);
        }

        // Simpan ke database
        DB::beginTransaction();
        try {
            $entry = JournalEntry::create([
                'source'  => $request->source,
                'tanggal' => $request->tanggal,
                'comment' => $request->comment,
            ]);

            $fiscalJournalMode = Setting::get('fiscal_journal_mode', 'disabled');

            foreach ($cleanedItems as $item) {
                $pajak = ($fiscalJournalMode === 'disabled') ? 0 : ($item['pajak'] ?? 0);
                $penyesuaianFiskal = ($fiscalJournalMode === 'disabled') ? null : ($item['penyesuaian_fiskal'] ?? null);
                $kodeFiscal = ($fiscalJournalMode === 'disabled') ? null : ($item['kode_fiscal'] ?? null);

                $entry->details()->create([
                    'kode_akun'          => $item['kode_akun'],
                    'departemen_akun_id' => $item['departemen_akun_id'] ?? null,
                    'debits'             => $item['debits'] ?? 0,
                    'credits'            => $item['credits'] ?? 0,
                    'comment'            => $item['comment'] ?? null,
                    'project_id'         => $item['project_id'] ?? null,
                    'pajak'              => $pajak,
                    'penyesuaian_fiskal' => $penyesuaianFiskal,
                    'kode_fiscal'        => $kodeFiscal,
                ]);
            }

            DB::commit();

            // Auto-recalculate jurnal penutup jika backyear
            $this->recalculateClosingIfBackyear($request->tanggal);

            return redirect()->route('journal_entry.index')
                ->with('success', 'Journal entry berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan journal entry: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menyimpan journal entry: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $journal = JournalEntry::with([
            'details.chartOfAccount',
            'details.departemenAkun.departemen'
        ])->findOrFail($id);

        return view('journal_entry.show', compact('journal'));
    }

    public function quickView($id)
    {
        $journal = JournalEntry::with([
            'details.chartOfAccount',
            'details.departemenAkun.departemen'
        ])->findOrFail($id);

        $editUrl = route('journal_entry.edit', $journal->id);
        $backUrl = request('back_url');
        if (empty($backUrl) && request()->headers->has('referer')) {
            $referer = request()->headers->get('referer');
            if (is_string($referer) && !empty($referer) && !Str::contains($referer, '/edit')) {
                $backUrl = $referer;
            }
        }
        if (!empty($backUrl)) {
            while (is_string($backUrl) && strpos($backUrl, '&amp;') !== false) {
                $backUrl = html_entity_decode($backUrl, ENT_QUOTES, 'UTF-8');
            }
            $editUrl .= '?back_url=' . urlencode($backUrl);
        }

        return response()->json([
            'id' => $journal->id,
            'source' => $journal->source,
            'tanggal' => $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('d-m-Y') : '-',
            'raw_tanggal' => $journal->tanggal,
            'comment' => $journal->comment ?? '-',
            'edit_url' => $editUrl,
            'show_url' => route('journal_entry.show', $journal->id),
            'details' => $journal->details->map(function ($d) {
                $dept = $d->departemenAkun ? $d->departemenAkun->departemen : null;
                return [
                    'id' => $d->id,
                    'kode_akun' => $d->kode_akun,
                    'nama_akun' => $d->chartOfAccount ? $d->chartOfAccount->nama_akun : '-',
                    'departemen' => $dept ? ($dept->kode . ' - ' . $dept->deskripsi) : 'Non-Departemen / Unassigned',
                    'departemen_kode' => $dept ? $dept->kode : 'Non-Dept',
                    'comment' => $d->comment ?? '-',
                    'debits' => (float) $d->debits,
                    'credits' => (float) $d->credits,
                    'debits_formatted' => number_format((float) $d->debits, 2, ',', '.'),
                    'credits_formatted' => number_format((float) $d->credits, 2, ',', '.'),
                ];
            }),
            'total_debits' => (float) $journal->details->sum('debits'),
            'total_credits' => (float) $journal->details->sum('credits'),
            'total_debits_formatted' => number_format((float) $journal->details->sum('debits'), 2, ',', '.'),
            'total_credits_formatted' => number_format((float) $journal->details->sum('credits'), 2, ',', '.'),
            'is_balanced' => abs($journal->details->sum('debits') - $journal->details->sum('credits')) < 0.01,
        ]);
    }

    public function destroy($id)
    {
        if (auth()->check() && !auth()->user()->can('journal_entry.delete')) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk menghapus voucher ini.'
                ], 403);
            }
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus voucher ini.');
        }

        DB::beginTransaction();

        try {
            // Temukan journal entry
            $entry = JournalEntry::findOrFail($id);
            $tanggal = $entry->tanggal; // Simpan tanggal sebelum dihapus
            $source = $entry->source;

            // Hapus detail terlebih dahulu
            JournalEntryDetail::where('journal_entry_id', $entry->id)->delete();

            // Hapus journal entry
            $entry->delete();

            DB::commit();

            // Auto-recalculate jurnal penutup jika backyear
            $this->recalculateClosingIfBackyear($tanggal);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Voucher {$source} berhasil dihapus."
                ]);
            }

            // --- Kembalikan ke state list sebelumnya ---
            $backUrl = request('back_url');

            // Validasi: hanya boleh redirect ke domain aplikasi sendiri (cegah open redirect)
            if ($this->isValidInternalUrl($backUrl)) {
                // Parse query string dari back_url untuk cek page
                $parsed   = parse_url($backUrl);
                $queryStr = $parsed['query'] ?? '';
                parse_str($queryStr, $params);
                $page = (int) ($params['page'] ?? 1);

                if ($page > 1) {
                    // Hitung sisa data dengan filter yang sama setelah delete
                    $countQuery = JournalEntry::query();
                    if (!empty($params['filter_bulan'])) {
                        $countQuery->whereMonth('tanggal', $params['filter_bulan']);
                    }
                    if (!empty($params['filter_tahun'])) {
                        $countQuery->whereYear('tanggal', $params['filter_tahun']);
                    }
                    if (!empty($params['search'])) {
                        $search = $params['search'];
                        $countQuery->where(function ($q) use ($search) {
                            $q->orWhere('source', 'like', "%{$search}%")
                              ->orWhere('comment', 'like', "%{$search}%")
                              ->orWhere('tanggal', 'like', "%{$search}%");
                        });
                    }
                    $totalAfterDelete = $countQuery->count();
                    $perPage          = 15;
                    $maxPage          = (int) ceil($totalAfterDelete / $perPage);

                    // Jika halaman aktif sudah kosong, mundur 1 halaman
                    if ($page > $maxPage && $maxPage >= 1) {
                        $params['page'] = $maxPage;
                        $newQuery = http_build_query($params);
                        $backUrl  = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '') .
                                    (isset($parsed['port']) ? ':' . $parsed['port'] : '') .
                                    ($parsed['path'] ?? '') .
                                    ($newQuery ? '?' . $newQuery : '');
                    } elseif ($maxPage === 0) {
                        // Semua data habis, kembali ke halaman 1
                        unset($params['page']);
                        $newQuery = http_build_query($params);
                        $backUrl  = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '') .
                                    (isset($parsed['port']) ? ':' . $parsed['port'] : '') .
                                    ($parsed['path'] ?? '') .
                                    ($newQuery ? '?' . $newQuery : '');
                    }
                }

                return redirect($backUrl)
                    ->with('success', 'Journal entry berhasil dihapus.');
            }

            return redirect()->route('journal_entry.index')
                ->with('success', 'Journal entry berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Gagal menghapus journal entry: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus journal entry: ' . $e->getMessage()
                ], 500);
            }

            $backUrl = request('back_url');
            $redirectTarget = $this->isValidInternalUrl($backUrl)
                ? redirect($backUrl)
                : redirect()->route('journal_entry.index');

            return $redirectTarget
                ->with('error', 'Gagal menghapus journal entry: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (auth()->check() && !auth()->user()->can('journal_entry.delete')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk menghapus voucher ini.'
                ], 403);
            }
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus voucher ini.');
        }

        $entryIds = $request->input('ids');
        if (is_string($entryIds)) {
            $entryIds = explode(',', $entryIds);
        }
        $entryIds = array_filter(array_unique(array_map('intval', (array) $entryIds)));

        if (empty($entryIds)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada voucher yang dipilih.'], 422);
            }
            return redirect()->back()->with('error', 'Tidak ada voucher yang dipilih.');
        }

        DB::beginTransaction();
        try {
            $entries = JournalEntry::whereIn('id', $entryIds)->get();
            $count = $entries->count();

            if ($count === 0) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Voucher yang dipilih tidak ditemukan.'], 404);
                }
                return redirect()->back()->with('error', 'Voucher yang dipilih tidak ditemukan.');
            }

            $tanggals = $entries->pluck('tanggal')->unique();

            JournalEntryDetail::whereIn('journal_entry_id', $entryIds)->delete();
            JournalEntry::whereIn('id', $entryIds)->delete();

            DB::commit();

            foreach ($tanggals as $tgl) {
                $this->recalculateClosingIfBackyear($tgl);
            }

            $msg = "{$count} voucher berhasil dihapus.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'deleted_count' => $count
                ]);
            }

            $backUrl = $request->input('back_url');
            if ($this->isValidInternalUrl($backUrl)) {
                return redirect($backUrl)->with('success', $msg);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus bulk journal entry: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus voucher: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus voucher: ' . $e->getMessage());
        }
    }

    public function truncate()
    {
        try {
            // Disable foreign key checks to allow truncating
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Truncate tables
            JournalEntryDetail::truncate();
            JournalEntry::truncate();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->route('journal_entry.index')
                ->with('success', 'Seluruh data Journal Entry berhasil dikosongkan.');
        } catch (\Exception $e) {
            Log::error('Gagal truncate journal entry: ' . $e->getMessage());

            return redirect()->route('journal_entry.index')
                ->with('error', 'Gagal mengosongkan data: ' . $e->getMessage());
        }
    }

    public function deleteByRange(Request $request)
    {
        $request->validate([
            'delete_type' => 'required|in:range,year',
            'start_date'  => 'required_if:delete_type,range|nullable|date',
            'end_date'    => 'required_if:delete_type,range|nullable|date|after_or_equal:start_date',
            'delete_year' => 'required_if:delete_type,year|nullable|integer|min:2000|max:2099',
        ]);

        try {
            DB::beginTransaction();

            $query = JournalEntry::query();

            if ($request->delete_type === 'range') {
                $query->whereDate('tanggal', '>=', $request->start_date)
                      ->whereDate('tanggal', '<=', $request->end_date);
                $keterangan = "periode {$request->start_date} s/d {$request->end_date}";
            } else {
                $query->whereYear('tanggal', $request->delete_year);
                $keterangan = "tahun {$request->delete_year}";
            }

            $entries = $query->get();
            $count   = $entries->count();

            if ($count === 0) {
                return redirect()->route('journal_entry.index')
                    ->with('error', 'Tidak ada data Journal Entry yang ditemukan untuk ' . $keterangan . '.');
            }

            // Hapus detail terlebih dahulu
            $ids = $entries->pluck('id');
            JournalEntryDetail::whereIn('journal_entry_id', $ids)->delete();
            JournalEntry::whereIn('id', $ids)->delete();

            DB::commit();

            return redirect()->route('journal_entry.index')
                ->with('success', "Berhasil menghapus {$count} Journal Entry untuk {$keterangan}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal hapus journal entry by range: ' . $e->getMessage());
            return redirect()->route('journal_entry.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function showFilterForm()
    {
        return view('journal_entry.filter_journal_entry');
    }

    public function journalEntryFilter(Request $request)
    {
        $query = JournalEntry::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $entries = $query->get();

        return view('journal_entry.filter_journal_entry', [
            'entries' => $entries,
            'not_found' => $entries->isEmpty() ? 'Data tidak ditemukan untuk filter yang dipilih.' : null,
        ]);
    }

    public function journalEntryShow()
    {
        return view('journal_entry.view_journal_entry');
    }

    public function journalEntryView(Request $request)
    {
        $query = JournalEntry::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $entries = $query->limit(100)->get();

        return view('journal_entry.view_journal_entry', [
            'entries' => $entries,
            'not_found' => $entries->isEmpty() ? 'Data tidak ditemukan untuk filter yang dipilih.' : null,
            'has_more' => $query->count() > 100, // Indicate if there are more records
        ]);
    }
    public function edit($id)
    {
        $journalEntry = JournalEntry::with(['details.chartOfAccount', 'details.departemenAkun.departemen', 'details.project'])->findOrFail($id);

        $projects = Project::all();
        $fiscalJournalMode = Setting::get('fiscal_journal_mode', 'disabled');
        return view('journal_entry.edit', compact('journalEntry', 'projects', 'fiscalJournalMode'));
    }
    public function update(Request $request, $id)
    {

        // dd($request->all());
        // Bersihkan dan filter input
        $cleanedItems = collect($request->items)
            ->filter(fn($item) => !empty($item['kode_akun']))
            ->map(function ($item) {
                $item['debits']  = $this->normalizeNumber($item['debits'] ?? null);
                $item['credits'] = $this->normalizeNumber($item['credits'] ?? null);
                $item['departemen_akun_id'] = !empty($item['departemen_akun_id']) ? (int) $item['departemen_akun_id'] : null;
                return $item;
            })
            ->values()
            ->all();
        // dump('RAW items:', $request->items);
        // dump('CLEANED items:', $cleanedItems);

        // Gabungkan hasil ke request agar validasi tetap bisa dipakai
        $request->merge(['items' => $cleanedItems]);

        // Validasi dasar
        $request->validate([
            'source' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'comment' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_akun' => 'required|string',
            'items.*.departemen_akun_id' => 'nullable|exists:departemen_akuns,id',
            'items.*.debits' => 'nullable|numeric',
            'items.*.credits' => 'nullable|numeric',
            'items.*.comment' => 'nullable|string',
            'items.*.project_id' => 'nullable|exists:projects,id',
            'items.*.pajak' => 'nullable|boolean',
            'items.*.penyesuaian_fiskal' => 'nullable|string',
            'items.*.kode_fiscal' => 'nullable|string',
        ]);

        // Validasi backyear: hanya boleh 1 tahun ke belakang (Admin = unlimited)
        $isAdmin = auth()->check() && auth()->user()->hasRole('Admin');
        if (!$isAdmin) {
            $periodeAktif = DB::table('start_new_years')->where('status', 'Opening')->first();
            $tahunAktif = $periodeAktif ? $periodeAktif->tahun : (int) date('Y');
            $minYear = $tahunAktif - 1; // Backyear 1 tahun

            $tahunInput = (int) date('Y', strtotime($request->tanggal));
            if ($tahunInput < $minYear) {
                return back()->withInput()->withErrors([
                    'tanggal' => "Transaksi hanya bisa diinput untuk tahun {$minYear} atau lebih baru (backyear maksimal 1 tahun)."
                ]);
            }
        }

        // ===== Validasi manual tambahan =====
        $totalDebit  = collect($cleanedItems)->sum(fn($i) => (float) $i['debits']);
        $totalCredit = collect($cleanedItems)->sum(fn($i) => (float) $i['credits']);
        // dump('TOTAL DEBIT:', $totalDebit, 'TOTAL CREDIT:', $totalCredit);

        // Gunakan toleransi float kecil (hindari mismatch 0.000001)
        if (abs($totalDebit - $totalCredit) > 0.001) {
            return back()->withInput()->withErrors([
                'items' => 'Total debit (' . number_format($totalDebit, 2, ',', '.') . ') dan kredit (' . number_format($totalCredit, 2, ',', '.') . ') harus sama.'
            ]);
        }

        // Pastikan tidak hanya satu sisi transaksi
        if (($totalDebit == 0 && $totalCredit > 0) || ($totalCredit == 0 && $totalDebit > 0)) {
            return back()->withInput()->withErrors([
                'items' => 'Harus ada lawan transaksi — tidak boleh hanya debit atau hanya kredit.'
            ]);
        }

        // Simpan ke database
        DB::beginTransaction();
        try {
            $entry = JournalEntry::findOrFail($id);

            // Update header
            $entry->update([
                'source'  => $request->source,
                'tanggal' => $request->tanggal,
                'comment' => $request->comment,
            ]);

            // Hapus detail lama
            $entry->details()->delete();

            $fiscalJournalMode = Setting::get('fiscal_journal_mode', 'disabled');

            // Simpan detail baru
            foreach ($cleanedItems as $item) {
                $pajak = ($fiscalJournalMode === 'disabled') ? 0 : ($item['pajak'] ?? 0);
                $penyesuaianFiskal = ($fiscalJournalMode === 'disabled') ? null : ($item['penyesuaian_fiskal'] ?? null);
                $kodeFiscal = ($fiscalJournalMode === 'disabled') ? null : ($item['kode_fiscal'] ?? null);

                $entry->details()->create([
                    'departemen_akun_id' => $item['departemen_akun_id'] ?? null,
                    'kode_akun'          => $item['kode_akun'],
                    'debits'             => $item['debits'] ?? 0,
                    'credits'            => $item['credits'] ?? 0,
                    'comment'            => $item['comment'] ?? null,
                    'project_id'         => $item['project_id'] ?? null,
                    'pajak'              => $pajak,
                    'penyesuaian_fiskal' => $penyesuaianFiskal,
                    'kode_fiscal'        => $kodeFiscal,
                ]);
            }

            DB::commit();

            // Auto-recalculate jurnal penutup jika backyear
            $this->recalculateClosingIfBackyear($request->tanggal);

            // --- Kembalikan ke state list sebelumnya ---
            $backUrl = $request->input('back_url');
            if (empty($backUrl) && $request->headers->has('referer')) {
                $referer = $request->headers->get('referer');
                if (is_string($referer) && !empty($referer) && !Str::contains($referer, '/edit')) {
                    $backUrl = $referer;
                }
            }
            if ($backUrl) {
                while (is_string($backUrl) && strpos($backUrl, '&amp;') !== false) {
                    $backUrl = html_entity_decode($backUrl, ENT_QUOTES, 'UTF-8');
                }
            }

            // Validasi: hanya boleh redirect ke domain aplikasi sendiri (cegah open redirect)
            $isInternal = $this->isValidInternalUrl($backUrl);
            $targetUrl = $isInternal ? $backUrl : route('journal_entry.index');

            if ($request->input('from_new_tab') == '1') {
                return view('journal_entry.tab_close_redirect', [
                    'backUrl' => $targetUrl,
                    'message' => 'Journal entry berhasil diperbarui.',
                ]);
            }

            return redirect($targetUrl)
                ->with('success', 'Journal entry berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update journal entry: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal update journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Auto-recalculate jurnal penutup jika transaksi adalah backyear
     * Efisien: hanya 1 query untuk cek, dan recalculate hanya jika tahun sudah closing
     */
    private function recalculateClosingIfBackyear($tanggal)
    {
        $tahunTransaksi = (int) date('Y', strtotime($tanggal));

        // Cek apakah tahun transaksi sudah di-closing (ada periode dengan status Closing)
        $isClosed = DB::table('start_new_years')
            ->where('tahun', $tahunTransaksi)
            ->where('status', 'Closing')
            ->exists();

        // Hanya recalculate jika tahun tersebut sudah closing
        if ($isClosed) {
            try {
                $service = new StartNewYearService();
                $service->updateLabaTahunBerjalan($tahunTransaksi);
            } catch (\Exception $e) {
                // Log error tapi jangan gagalkan transaksi utama
                Log::warning('Gagal recalculate closing entries: ' . $e->getMessage());
            }
        }
    }

    /**
     * Validasi URL: hanya boleh redirect ke domain internal aplikasi sendiri (cegah open redirect).
     * Mendukung URL internal absolut maupun path relatif yang aman.
     */
    private function isValidInternalUrl(&$url)
    {
        if (empty($url) || !is_string($url)) {
            return false;
        }

        // Decode HTML entities (misal &amp; -> &)
        while (is_string($url) && strpos($url, '&amp;') !== false) {
            $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
        }

        // Normalisasi path relatif aman (contoh: /laporan/buku-besar?start_date=...)
        // Tolak protocol-relative '//' dan backslash-prefixed '/\'
        if (Str::startsWith($url, '/') && !Str::startsWith($url, '//') && !Str::startsWith($url, '/\\')) {
            $url = url($url);
        }

        $appUrl = url('/');
        if (Str::startsWith($url, $appUrl) && (
            $url === $appUrl ||
            Str::startsWith($url, $appUrl . '/') ||
            Str::startsWith($url, $appUrl . '?')
        )) {
            return true;
        }

        // Toleransi domain/host: jika host URL sama dengan host request saat ini atau config app.url
        $parsedUrl = parse_url($url);
        $currentHost = request()->getHost();
        $configAppUrl = config('app.url');
        $configHost = $configAppUrl ? parse_url($configAppUrl, PHP_URL_HOST) : null;
        if (!empty($parsedUrl['host'])) {
            $targetHost = strtolower($parsedUrl['host']);
            if ($targetHost === strtolower($currentHost) || ($configHost && $targetHost === strtolower($configHost))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalisasi nilai input angka (format Indonesia atau internasional) menjadi float.
     */
    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = preg_replace('/[^\d.,-]/', '', (string) $value);

        // Format Indonesia: mengandung koma (misal 1.000,50 atau 1000,50 atau 0,00)
        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
            return (float) $value;
        }

        // Jika tidak ada koma, tapi ada titik ribuan (misal 39.500.000 atau 39.500)
        if (substr_count($value, '.') > 1 || preg_match('/\.\d{3}$/', $value)) {
            $value = str_replace('.', '', $value);
            return (float) $value;
        }

        // Format internasional desimal (misal 1000.50)
        return (float) $value;
    }
}
