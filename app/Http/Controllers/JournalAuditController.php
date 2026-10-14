<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Departement;
use App\JournalAuditAnomaly;
use App\KamusIndikatorAkun;
use App\Services\JournalAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:journal_entry.access');
    }

    /**
     * Helper untuk membangun Query Filter Anomali Jurnal
     */
    protected function buildFilteredQuery(Request $request, $ignoreStatusAndResolved = false)
    {
        $query = JournalAuditAnomaly::with(['rule', 'currentAccount', 'recommendedAccount', 'departemen', 'resolver']);

        $startDate            = $request->get('start_date');
        $endDate              = $request->get('end_date');
        $status               = $request->get('status', 'all');
        $resolved             = $request->get('is_resolved', '0');
        $search               = $request->get('search');
        $sourcePrefix         = $request->get('source_prefix', 'all');
        $currentKodeAkun      = $request->get('current_kode_akun');
        $recommendedKodeAkun  = $request->get('recommended_kode_akun');
        $mutationType         = $request->get('mutation_type', 'all');
        $minAmount            = $request->get('min_amount');
        $maxAmount            = $request->get('max_amount');
        $departemenId         = $request->get('departemen_id', 'all');
        $ruleId               = $request->get('rule_id', 'all');

        // Filter Tanggal
        if (!empty($startDate)) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        // Filter Status & Penyelesaian (dilewati jika menghitung base KPI cards)
        if (!$ignoreStatusAndResolved) {
            if ($status !== 'all' && !empty($status)) {
                $query->where('status', $status);
            }
            if ($resolved !== 'all' && $resolved !== null && $resolved !== '') {
                $query->where('is_resolved', $resolved === '1');
            }
        }

        // Filter Prefix / Sumber Voucher
        if ($sourcePrefix !== 'all' && !empty($sourcePrefix)) {
            $query->where('voucher', 'like', $sourcePrefix . '%');
        }

        // Filter Akun Saat Ini
        if (!empty($currentKodeAkun) && $currentKodeAkun !== 'all') {
            $query->where('current_kode_akun', $currentKodeAkun);
        }

        // Filter Rekomendasi Akun
        if (!empty($recommendedKodeAkun) && $recommendedKodeAkun !== 'all') {
            if ($recommendedKodeAkun === '__HAS_REC__') {
                $query->whereNotNull('recommended_kode_akun')->where('recommended_kode_akun', '!=', '');
            } elseif ($recommendedKodeAkun === '__NO_REC__') {
                $query->where(function ($q) {
                    $q->whereNull('recommended_kode_akun')->orWhere('recommended_kode_akun', '');
                });
            } else {
                $query->where('recommended_kode_akun', $recommendedKodeAkun);
            }
        }

        // Filter Departemen
        if ($departemenId !== 'all' && !empty($departemenId)) {
            $query->where('departemen_id', $departemenId);
        }

        // Filter Rule Kamus
        if ($ruleId !== 'all' && !empty($ruleId)) {
            $query->where('rule_id', $ruleId);
        }

        // Parser Nominal Angka
        $parseAmount = function ($val) {
            if ($val === null || $val === '') {
                return null;
            }
            $clean = str_replace([' ', 'Rp', 'rp'], '', trim($val));
            if (is_numeric($clean)) {
                return (float) $clean;
            }
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
            return is_numeric($clean) ? (float) $clean : null;
        };

        $cleanMin = $parseAmount($minAmount);
        $cleanMax = $parseAmount($maxAmount);

        // Filter Mutasi & Nominal
        if ($mutationType === 'debit') {
            $query->where('debits', '>', 0);
            if ($cleanMin !== null) {
                $query->where('debits', '>=', $cleanMin);
            }
            if ($cleanMax !== null) {
                $query->where('debits', '<=', $cleanMax);
            }
        } elseif ($mutationType === 'credit') {
            $query->where('credits', '>', 0);
            if ($cleanMin !== null) {
                $query->where('credits', '>=', $cleanMin);
            }
            if ($cleanMax !== null) {
                $query->where('credits', '<=', $cleanMax);
            }
        } else {
            if ($cleanMin !== null && $cleanMax !== null) {
                $query->where(function ($q) use ($cleanMin, $cleanMax) {
                    $q->whereBetween('debits', [$cleanMin, $cleanMax])
                      ->orWhereBetween('credits', [$cleanMin, $cleanMax]);
                });
            } elseif ($cleanMin !== null) {
                $query->where(function ($q) use ($cleanMin) {
                    $q->where('debits', '>=', $cleanMin)
                      ->orWhere('credits', '>=', $cleanMin);
                });
            } elseif ($cleanMax !== null) {
                $query->where(function ($q) use ($cleanMax) {
                    $q->where(function ($sub) use ($cleanMax) {
                        $sub->where('debits', '<=', $cleanMax)->where('debits', '>', 0);
                    })->orWhere(function ($sub) use ($cleanMax) {
                        $sub->where('credits', '<=', $cleanMax)->where('credits', '>', 0);
                    });
                });
            }
        }

        // Pencarian Teks
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhere('current_kode_akun', 'like', "%{$search}%")
                  ->orWhere('current_nama_akun', 'like', "%{$search}%")
                  ->orWhere('recommended_kode_akun', 'like', "%{$search}%")
                  ->orWhere('recommended_nama_akun', 'like', "%{$search}%")
                  ->orWhere('alasan_diagnosa', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Halaman Utama Control Center Audit & Kamus Jurnal
     */
    public function index(Request $request)
    {
        $startDate           = $request->get('start_date');
        $endDate             = $request->get('end_date');
        $status              = $request->get('status', 'all');
        $resolved            = $request->get('is_resolved', '0'); // default hanya tampilkan yg belum diselesaikan
        $search              = $request->get('search');
        $sourcePrefix        = $request->get('source_prefix', 'all');
        $currentKodeAkun     = $request->get('current_kode_akun');
        $recommendedKodeAkun = $request->get('recommended_kode_akun');
        $mutationType        = $request->get('mutation_type', 'all');
        $minAmount           = $request->get('min_amount');
        $maxAmount           = $request->get('max_amount');
        $departemenId        = $request->get('departemen_id', 'all');
        $ruleId              = $request->get('rule_id', 'all');
        $perPage             = (int) $request->get('per_page', 50);

        if ($perPage <= 0 || $perPage > 200) {
            $perPage = 50;
        }

        // Query anomalies dengan seluruh filter
        $query = $this->buildFilteredQuery($request, false)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        $anomalies = $query->paginate($perPage)->appends($request->query());

        // Statistik Keseluruhan (Dinamis: menghitung berdasarkan irisan filter aktif yang dipilih)
        $baseStatsQuery = $this->buildFilteredQuery($request, true);

        $stats = [
            'total'      => (clone $baseStatsQuery)->count(),
            'salah_akun' => (clone $baseStatsQuery)->where('status', 'SALAH_AKUN')->where('is_resolved', false)->count(),
            'ambigu'     => (clone $baseStatsQuery)->where('status', 'AMBIGU_BIAS')->where('is_resolved', false)->count(),
            'pola_baru'  => (clone $baseStatsQuery)->where('status', 'POLA_BARU')->where('is_resolved', false)->count(),
            'tepat'      => (clone $baseStatsQuery)->where('status', 'TEPAT')->count(),
            'resolved'   => (clone $baseStatsQuery)->where('is_resolved', true)->count(),
        ];

        // Hitung filter lanjutan aktif untuk badge dan auto-expand
        $activeAdvancedCount = 0;
        if (!empty($currentKodeAkun) && $currentKodeAkun !== 'all') $activeAdvancedCount++;
        if (!empty($recommendedKodeAkun) && $recommendedKodeAkun !== 'all') $activeAdvancedCount++;
        if (!empty($mutationType) && $mutationType !== 'all') $activeAdvancedCount++;
        if ($minAmount !== null && $minAmount !== '') $activeAdvancedCount++;
        if ($maxAmount !== null && $maxAmount !== '') $activeAdvancedCount++;
        if (!empty($departemenId) && $departemenId !== 'all') $activeAdvancedCount++;
        if (!empty($sourcePrefix) && $sourcePrefix !== 'all') $activeAdvancedCount++;
        if (!empty($ruleId) && $ruleId !== 'all') $activeAdvancedCount++;

        $hasAdvancedFilters = $activeAdvancedCount > 0;

        // Master data untuk dropdown
        $coas = ChartOfAccount::select('kode_akun', 'nama_akun', 'tipe_akun')
            ->orderBy('kode_akun')
            ->get();

        $rules = KamusIndikatorAkun::orderBy('urutan')->get();

        $departemens = Departement::select('id', 'kode', 'deskripsi')
            ->orderBy('kode')
            ->get();

        $popularPrefixes = ['BNI', 'CH-', 'KAS', 'BCA', 'MDR', 'JAG', 'BRI', 'LR-', 'GAJ', 'NRC'];

        return view('journal_audit.index', compact(
            'anomalies',
            'stats',
            'coas',
            'rules',
            'departemens',
            'popularPrefixes',
            'startDate',
            'endDate',
            'status',
            'resolved',
            'search',
            'sourcePrefix',
            'currentKodeAkun',
            'recommendedKodeAkun',
            'mutationType',
            'minAmount',
            'maxAmount',
            'departemenId',
            'ruleId',
            'perPage',
            'hasAdvancedFilters',
            'activeAdvancedCount'
        ));
    }

    /**
     * Export Hasil Filter Audit ke CSV (Kompatibel Excel)
     */
    public function export(Request $request)
    {
        $query = $this->buildFilteredQuery($request, false)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        $filename = 'journal_audit_export_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Kolom
            fputcsv($file, [
                'ID Anomali',
                'Tanggal',
                'Nomor Voucher',
                'Departemen',
                'Keterangan (Comment Line)',
                'Posisi Mutasi',
                'Debit (Rp)',
                'Kredit (Rp)',
                'Nominal (Rp)',
                'Kode Akun Saat Ini',
                'Nama Akun Saat Ini',
                'Status Audit',
                'Kode Rekomendasi Akun',
                'Nama Rekomendasi Akun',
                'Alasan Diagnosa Kamus',
                'Status Penyelesaian',
                'Waktu Diselesaikan',
                'Diselesaikan Oleh',
            ]);

            $query->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    $isDebit = (float) $row->debits > 0;
                    $amount = max((float) $row->debits, (float) $row->credits);
                    $deptName = $row->departemen ? ($row->departemen->kode . ' - ' . $row->departemen->deskripsi) : '-';
                    $resolverName = $row->resolver ? $row->resolver->name : '-';
                    $resolvedTime = $row->resolved_at ? Carbon::parse($row->resolved_at)->format('d/m/Y H:i') : '-';

                    fputcsv($file, [
                        $row->id,
                        $row->tanggal ? $row->tanggal->format('d/m/Y') : '-',
                        $row->voucher,
                        $deptName,
                        $row->comment ?: '',
                        $isDebit ? 'Debit' : 'Kredit',
                        (float) $row->debits,
                        (float) $row->credits,
                        $amount,
                        $row->current_kode_akun,
                        $row->current_nama_akun,
                        $row->status,
                        $row->recommended_kode_akun ?: '-',
                        $row->recommended_nama_akun ?: '-',
                        $row->alasan_diagnosa ?: '',
                        $row->is_resolved ? 'Selesai' : 'Pending',
                        $resolvedTime,
                        $resolverName,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menjalankan Pemindaian Jurnal On-Demand
     */
    public function scan(Request $request, JournalAuditService $service)
    {
        $startDate    = $request->get('start_date');
        $endDate      = $request->get('end_date');
        $sourcePrefix = $request->get('source_prefix');
        $limit        = $request->get('limit') ? (int) $request->get('limit') : null;

        $result = $service->scanTransactions($startDate, $endDate, $limit, $sourcePrefix);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pemindaian selesai: {$result['total_scanned']} detail diperiksa. " .
                             "Ditemukan {$result['salah_akun']} Salah Akun, {$result['ambigu']} Ambigu/Bias, {$result['pola_baru']} Pola Baru.",
                'data' => $result,
            ]);
        }

        return redirect()->route('journal_audit.index', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ])->with('success', "Pemindaian berhasil! Total {$result['total_scanned']} transaksi dipindai ({$result['salah_akun']} Salah Akun, {$result['ambigu']} Ambigu, {$result['tepat']} Tepat).");
    }

    /**
     * Memperbaiki Akun Secara Satuan
     */
    public function remediateSingle(Request $request, JournalAuditService $service)
    {
        $request->validate([
            'anomaly_id'     => 'required|integer',
            'new_kode_akun'  => 'required|string',
            'alasan'         => 'nullable|string',
        ]);

        try {
            $userId = Auth::id();
            $result = $service->remediateSingle(
                (int) $request->anomaly_id,
                $request->new_kode_akun,
                $userId,
                $request->alasan
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbaiki jurnal: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Memperbaiki Akun Secara Masal (Bulk Re-assign)
     */
    public function remediateBulk(Request $request, JournalAuditService $service)
    {
        $request->validate([
            'anomaly_ids' => 'required|array',
            'anomaly_ids.*' => 'integer',
            'alasan'        => 'nullable|string',
        ]);

        try {
            $userId = Auth::id();
            $result = $service->remediateBulk(
                $request->anomaly_ids,
                $userId,
                $request->alasan
            );

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbaiki {$result['remediated']} transaksi secara masal (Gagal: {$result['failed']}).",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal remediasi massal: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Menandai Anomali sebagai Dikonfirmasi / Diabaikan
     */
    public function ignoreAnomaly(Request $request, $id)
    {
        $anomaly = JournalAuditAnomaly::findOrFail($id);
        $anomaly->update([
            'is_resolved'     => true,
            'resolved_at'     => now(),
            'resolved_by'     => Auth::id(),
            'resolved_action' => 'confirmed_valid',
            'alasan_diagnosa' => $anomaly->alasan_diagnosa . ' [Dikonfirmasi valid oleh auditor: ' . ($request->alasan ?: 'Sesuai kebijakan') . ']',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil ditandai sebagai terkonfirmasi/diabaikan.',
        ]);
    }

    /**
     * Halaman Kelola Kamus Indikator
     */
    public function rulesIndex()
    {
        $rules = KamusIndikatorAkun::orderBy('kategori')->orderBy('urutan')->get();
        $coas  = ChartOfAccount::select('kode_akun', 'nama_akun', 'tipe_akun')->orderBy('kode_akun')->get();

        return view('journal_audit.rules', compact('rules', 'coas'));
    }

    /**
     * Simpan Aturan Baru ke Kamus
     */
    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'kategori'             => 'required|string|max:50',
            'nama_indikator'       => 'required|string|max:255',
            'keyword_pattern'      => 'required|string',
            'match_type'           => 'required|in:contains,exact,regex',
            'sumber_dana_prefix'   => 'nullable|string|max:50',
            'arah_mutasi'          => 'required|in:debit,kredit,any',
            'min_amount'           => 'nullable|numeric|min:0',
            'max_amount'           => 'nullable|numeric|min:0',
            'target_kode_akun'     => 'required|string|exists:chart_of_accounts,kode_akun',
            'alternate_kode_akuns' => 'nullable|array',
            'tingkat_kepastian'    => 'required|in:PASTI,AMBIGU_REVIEW,KONDISIONAL',
            'penjelasan_aturan'    => 'nullable|string',
            'urutan'               => 'nullable|integer',
        ]);

        $coa = ChartOfAccount::where('kode_akun', $data['target_kode_akun'])->first();
        $data['target_nama_akun'] = $coa ? $coa->nama_akun : null;
        $data['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        KamusIndikatorAkun::create($data);

        return redirect()->route('journal_audit.rules')->with('success', 'Aturan kamus baru berhasil ditambahkan.');
    }

    /**
     * Update Aturan Kamus
     */
    public function updateRule(Request $request, $id)
    {
        $rule = KamusIndikatorAkun::findOrFail($id);

        $data = $request->validate([
            'kategori'             => 'required|string|max:50',
            'nama_indikator'       => 'required|string|max:255',
            'keyword_pattern'      => 'required|string',
            'match_type'           => 'required|in:contains,exact,regex',
            'sumber_dana_prefix'   => 'nullable|string|max:50',
            'arah_mutasi'          => 'required|in:debit,kredit,any',
            'min_amount'           => 'nullable|numeric|min:0',
            'max_amount'           => 'nullable|numeric|min:0',
            'target_kode_akun'     => 'required|string|exists:chart_of_accounts,kode_akun',
            'alternate_kode_akuns' => 'nullable|array',
            'tingkat_kepastian'    => 'required|in:PASTI,AMBIGU_REVIEW,KONDISIONAL',
            'penjelasan_aturan'    => 'nullable|string',
            'urutan'               => 'nullable|integer',
        ]);

        $coa = ChartOfAccount::where('kode_akun', $data['target_kode_akun'])->first();
        $data['target_nama_akun'] = $coa ? $coa->nama_akun : null;
        $data['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;

        $rule->update($data);

        return redirect()->route('journal_audit.rules')->with('success', 'Aturan kamus berhasil diperbarui.');
    }

    /**
     * Hapus Aturan Kamus
     */
    public function destroyRule($id)
    {
        $rule = KamusIndikatorAkun::findOrFail($id);
        $rule->delete();

        return redirect()->route('journal_audit.rules')->with('success', 'Aturan kamus berhasil dihapus.');
    }
}
