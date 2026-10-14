<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Departement;
use App\Exports\BukuBesarExport;
use App\JournalEntryDetail;
use App\StartNewYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuBesarController extends Controller
{
    public function bukuBesarFilter(Request $request)
    {
        $account = ChartOfAccount::all();
        $tahun_buku = StartNewYear::all();
        $departemen = Departement::orderBy('kode')->get();
        return view('buku_besar.filter_buku_besar', compact('account', 'tahun_buku', 'departemen'));
    }

    private function applyDepartmentFilter($query, $deptId)
    {
        if ($deptId === 'unassigned' || $deptId === 'none') {
            $query->whereNull('journal_entry_details.departemen_akun_id');
        } elseif (!empty($deptId) && $deptId !== 'all') {
            $query->whereHas('departemenAkun', function ($dq) use ($deptId) {
                $dq->where('departemen_id', $deptId);
            });
        }
        return $query;
    }

    private function getBukuBesarData(Request $request)
    {
        // Normalisasi parameter jika query string ter-escape HTML entity (&amp;)
        if (!$request->filled('end_date') && $request->filled('amp;end_date')) {
            $request->merge(['end_date' => $request->input('amp;end_date')]);
        }
        if (!$request->filled('selected_accounts') && $request->filled('amp;selected_accounts')) {
            $request->merge(['selected_accounts' => $request->input('amp;selected_accounts')]);
        }
        if (!$request->filled('departemen_id') && $request->filled('amp;departemen_id')) {
            $request->merge(['departemen_id' => $request->input('amp;departemen_id')]);
        }
        if (!$request->filled('sort_by') && $request->filled('amp;sort_by')) {
            $request->merge(['sort_by' => $request->input('amp;sort_by')]);
        }
        if (!$request->filled('show_lawan') && $request->filled('amp;show_lawan')) {
            $request->merge(['show_lawan' => $request->input('amp;show_lawan')]);
        }

        $accounts = ChartOfAccount::all();
        $tahun_buku = StartNewYear::all();

        // Ambil kode akun dari input
        $selectedAccountsRaw = explode(',', $request->selected_accounts ?? '');
        $selectedAccountCodes = [];
        foreach ($selectedAccountsRaw as $item) {
            $parts = explode(' - ', $item);
            $code = trim($parts[0] ?? '');
            if ($code !== '') {
                $selectedAccountCodes[] = $code;
            }
        }
        $selectedAccountCodes = array_values(array_unique($selectedAccountCodes));

        // 1. Query utama
        $rowsQuery = JournalEntryDetail::select(
            'journal_entry_details.id',
            'journal_entry_details.kode_akun',
            'journal_entry_details.debits',
            'journal_entry_details.credits',
            'journal_entry_details.comment',
            'journal_entry_details.journal_entry_id',
            'journal_entry_details.departemen_akun_id',
            'journal_entries.tanggal',
            'journal_entries.source'
        )
            ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
            ->with(['chartOfAccount', 'journalEntry', 'departemenAkun.departemen'])
            // Filter: jangan tampilkan jurnal penutup/transfer Start New Year
            ->where('journal_entries.source', '!=', 'START NEW YEAR')
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('journal_entries.tanggal', '>=', $request->start_date)
                    ->whereDate('journal_entries.tanggal', '<=', $request->end_date);
            })
            ->when(!empty($selectedAccountCodes), function ($q) use ($selectedAccountCodes) {
                $q->whereIn('journal_entry_details.kode_akun', $selectedAccountCodes);
            });

        $this->applyDepartmentFilter($rowsQuery, $request->departemen_id);

        $sortBy = $request->get('sort_by', 'date');
        $rowsQuery->orderBy('journal_entry_details.kode_akun', 'asc');
        if ($sortBy === 'transaction_number') {
            $rowsQuery->orderBy('journal_entries.source', 'asc')
                ->orderBy('journal_entries.tanggal', 'asc')
                ->orderBy('journal_entry_details.id', 'asc');
        } else {
            // Default: date
            $rowsQuery->orderBy('journal_entries.tanggal', 'asc')
                ->orderBy('journal_entries.source', 'asc')
                ->orderBy('journal_entry_details.id', 'asc');
        }

        $rows = $rowsQuery->get();

        // 1b. Resolusi Lawan Transaksi (Contra Account) secara efisien (Batch Query tanpa N+1)
        if ($rows->isNotEmpty()) {
            $journalEntryIds = $rows->pluck('journal_entry_id')->filter()->unique();
            $voucherDetails = JournalEntryDetail::select(
                'id',
                'journal_entry_id',
                'kode_akun',
                'debits',
                'credits'
            )
                ->whereIn('journal_entry_id', $journalEntryIds)
                ->with('chartOfAccount:kode_akun,nama_akun')
                ->get()
                ->groupBy('journal_entry_id');

            foreach ($rows as $row) {
                $lines = $voucherDetails->get($row->journal_entry_id, collect());
                $isDebit = (float) $row->debits > 0;
                $isCredit = (float) $row->credits > 0;

                // Lawan transaksi berada di sisi berseberangan
                if ($isDebit) {
                    $contraLines = $lines->where('id', '!=', $row->id)->filter(function ($l) {
                        return (float) $l->credits > 0;
                    });
                } elseif ($isCredit) {
                    $contraLines = $lines->where('id', '!=', $row->id)->filter(function ($l) {
                        return (float) $l->debits > 0;
                    });
                } else {
                    $contraLines = $lines->where('id', '!=', $row->id);
                }

                // Fallback jika voucher satu sisi
                if ($contraLines->isEmpty()) {
                    $contraLines = $lines->where('id', '!=', $row->id);
                }

                $contraNames = [];
                $contraCodes = [];
                foreach ($contraLines as $cl) {
                    $code = $cl->kode_akun;
                    $name = optional($cl->chartOfAccount)->nama_akun;
                    if ($code && !in_array($code, $contraCodes, true)) {
                        $contraCodes[] = $code;
                    }
                    if ($name && !in_array($name, $contraNames, true)) {
                        $contraNames[] = $name;
                    }
                }

                $cnt = count($contraNames);
                $row->contra_count = $cnt;
                $row->contra_first = $cnt > 0 ? $contraNames[0] : '-';
                $row->contra_others = $cnt > 1 ? array_slice($contraNames, 1) : [];
                $row->contra_account_name = $cnt > 0 ? implode(', ', $contraNames) : '-';
                $row->contra_codes = !empty($contraCodes) ? implode(' ', $contraCodes) : '';
            }
        }

        // 2. Hitung saldo awal
        $saldoAwalPerAkun = [];
        if ($request->filled('start_date')) {
            $startOfYear = \Carbon\Carbon::parse($request->start_date)->startOfYear()->toDateString();
            $isStartOfYear = ($request->start_date <= $startOfYear);

            // 2a. Saldo awal untuk akun riil (Aset, Kewajiban, Ekuitas): semua transaksi sebelum start_date
            $saldoAwalRiilQuery = JournalEntryDetail::select(
                'journal_entry_details.kode_akun',
                DB::raw('SUM(journal_entry_details.debits) as total_debit'),
                DB::raw('SUM(journal_entry_details.credits) as total_kredit')
            )
                ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.tanggal', '<', $request->start_date)
                // Filter: jangan termasuk jurnal penutup Start New Year
                ->where('journal_entries.source', '!=', 'START NEW YEAR')
                ->when(!empty($selectedAccountCodes), function ($q) use ($selectedAccountCodes) {
                    $q->whereIn('journal_entry_details.kode_akun', $selectedAccountCodes);
                });

            $this->applyDepartmentFilter($saldoAwalRiilQuery, $request->departemen_id);

            $saldoAwalRiil = $saldoAwalRiilQuery->groupBy('journal_entry_details.kode_akun')
                ->get()
                ->keyBy('kode_akun');

            // 2b. Saldo awal untuk akun nominal (Pendapatan, Beban): YTD transaksi (startOfYear <= tanggal < start_date)
            $saldoAwalNominal = collect();
            if (!$isStartOfYear) {
                $saldoAwalNominalQuery = JournalEntryDetail::select(
                    'journal_entry_details.kode_akun',
                    DB::raw('SUM(journal_entry_details.debits) as total_debit'),
                    DB::raw('SUM(journal_entry_details.credits) as total_kredit')
                )
                    ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entries.tanggal', '>=', $startOfYear)
                    ->where('journal_entries.tanggal', '<', $request->start_date)
                    ->where('journal_entries.source', '!=', 'START NEW YEAR')
                    ->when(!empty($selectedAccountCodes), function ($q) use ($selectedAccountCodes) {
                        $q->whereIn('journal_entry_details.kode_akun', $selectedAccountCodes);
                    });

                $this->applyDepartmentFilter($saldoAwalNominalQuery, $request->departemen_id);

                $saldoAwalNominal = $saldoAwalNominalQuery->groupBy('journal_entry_details.kode_akun')
                    ->get()
                    ->keyBy('kode_akun');
            }

            $allSaldoCodes = $saldoAwalRiil->keys()->merge($saldoAwalNominal->keys())->unique();

            foreach ($allSaldoCodes as $kodeAkun) {
                $akun = $accounts->firstWhere('kode_akun', $kodeAkun);
                if (!$akun) continue;

                $tipe = strtolower($akun->tipe_akun);

                if (in_array($tipe, ['pendapatan', 'beban'])) {
                    if ($isStartOfYear) {
                        $saldoAwalPerAkun[$kodeAkun] = 0;
                    } else {
                        $saldo = $saldoAwalNominal->get($kodeAkun);
                        $debit = $saldo->total_debit ?? 0;
                        $kredit = $saldo->total_kredit ?? 0;
                        if ($tipe === 'pendapatan') {
                            $saldoAwalPerAkun[$kodeAkun] = $kredit - $debit;
                        } else {
                            $saldoAwalPerAkun[$kodeAkun] = $debit - $kredit;
                        }
                    }
                } elseif (in_array($tipe, ['kewajiban', 'ekuitas'])) {
                    $saldo = $saldoAwalRiil->get($kodeAkun);
                    $saldoAwalPerAkun[$kodeAkun] = ($saldo->total_kredit ?? 0) - ($saldo->total_debit ?? 0);
                } else {
                    // Aset dan lainnya
                    $saldo = $saldoAwalRiil->get($kodeAkun);
                    $saldoAwalPerAkun[$kodeAkun] = ($saldo->total_debit ?? 0) - ($saldo->total_kredit ?? 0);
                }
            }
        }

        // 3. Grouping by account & Zero-balance handling
        $hideZero = $request->has('hide_zero')
            ? filter_var($request->input('hide_zero'), FILTER_VALIDATE_BOOLEAN)
            : true;

        $rowsByAccount = $rows->groupBy('kode_akun');

        if (!empty($selectedAccountCodes)) {
            $candidateAccounts = $accounts->whereIn('kode_akun', $selectedAccountCodes);
        } else {
            $candidateAccounts = $accounts;
        }

        $groupedByAccount = collect();

        foreach ($candidateAccounts->sortBy('kode_akun') as $account) {
            $kode = $account->kode_akun;
            $hasRows = $rowsByAccount->has($kode) && $rowsByAccount->get($kode)->isNotEmpty();
            $saldoAwal = $saldoAwalPerAkun[$kode] ?? 0;
            $hasNonZeroSaldo = abs($saldoAwal) > 0.0001;

            if ($hideZero) {
                // Sembunyikan akun jika tidak ada mutasi DAN saldo awal 0
                if (!$hasRows && !$hasNonZeroSaldo) {
                    continue;
                }
            }

            $accountRows = $rowsByAccount->get($kode, collect());
            $accountRows->account = $account;
            $groupedByAccount->put($kode, $accountRows);
        }

        // Pastikan akun dari $rows yang mungkin belum tercakup di candidateAccounts tetap ada
        foreach ($rowsByAccount as $kode => $accRows) {
            if (!$groupedByAccount->has($kode)) {
                $accRows->account = $accRows->first()->chartOfAccount ?? $accounts->firstWhere('kode_akun', $kode);
                $groupedByAccount->put($kode, $accRows);
            }
        }

        // 4. Hitung total per tipe akun
        $totalByType = [
            'pendapatan' => 0,
            'kewajiban'  => 0,
            'ekuitas'    => 0,
        ];
        foreach ($rows as $row) {
            $tipe = strtolower($row->chartOfAccount->tipe_akun ?? '');
            if (in_array($tipe, ['pendapatan', 'kewajiban', 'ekuitas'])) {
                $totalByType[$tipe] += $row->credits - $row->debits;
            }
        }

        $departemen = Departement::orderBy('kode')->get();

        $selectedDeptId = $request->departemen_id;
        $isConsolidated = empty($selectedDeptId) || $selectedDeptId === 'all';
        $departemenName = 'Semua Departemen (Konsolidasi)';
        if ($selectedDeptId === 'unassigned' || $selectedDeptId === 'none') {
            $departemenName = 'Non-Departemen / Unassigned';
        } elseif (!empty($selectedDeptId) && $selectedDeptId !== 'all') {
            $matchedDept = $departemen->firstWhere('id', $selectedDeptId);
            if ($matchedDept) {
                $departemenName = $matchedDept->kode . ' - ' . $matchedDept->deskripsi;
            } else {
                $departemenName = 'Departemen #' . $selectedDeptId;
            }
        }

        // 5. Mini Breakdown Mutasi Departemen per Akun (Mode Konsolidasi)
        $accountDeptBreakdowns = [];
        if ($isConsolidated) {
            foreach ($groupedByAccount as $kode => $accRows) {
                if ($accRows->isNotEmpty()) {
                    $breakdown = [];
                    foreach ($accRows as $r) {
                        $deptObj = $r->departemenAkun ? $r->departemenAkun->departemen : null;
                        $deptKey = $deptObj ? $deptObj->kode : 'unassigned';
                        if (!isset($breakdown[$deptKey])) {
                            $breakdown[$deptKey] = [
                                'kode'      => $deptObj ? $deptObj->kode : 'Non-Dept',
                                'deskripsi' => $deptObj ? $deptObj->deskripsi : 'Non-Departemen / Unassigned',
                                'debit'     => 0,
                                'kredit'    => 0,
                            ];
                        }
                        $breakdown[$deptKey]['debit']  += (float) $r->debits;
                        $breakdown[$deptKey]['kredit'] += (float) $r->credits;
                    }
                    $accountDeptBreakdowns[$kode] = array_values($breakdown);
                }
            }
        }

        // 6. KPI Summary
        $totalActiveAccounts = $groupedByAccount->filter(function ($accRows, $kode) use ($saldoAwalPerAkun) {
            $hasRows = $accRows->isNotEmpty();
            $saldoAwal = $saldoAwalPerAkun[$kode] ?? 0;
            return $hasRows || abs($saldoAwal) > 0.0001;
        })->count();

        $kpiSummary = [
            'total_active_accounts' => $totalActiveAccounts,
            'total_debit'           => (float) $rows->sum('debits'),
            'total_credit'          => (float) $rows->sum('credits'),
            'net_movement'          => (float) ($rows->sum('debits') - $rows->sum('credits')),
        ];

        return [
            'accounts'               => $accounts,
            'rows'                   => $rows,
            'groupedByAccount'       => $groupedByAccount,
            'start_date'             => $request->start_date,
            'end_date'               => $request->end_date,
            'saldoAwalPerAkun'       => $saldoAwalPerAkun,
            'totalByType'            => $totalByType,
            'tahun_buku'             => $tahun_buku,
            'departemen'             => $departemen,
            'departemen_name'        => $departemenName,
            'selected_departemen_id' => $selectedDeptId,
            'is_consolidated'        => $isConsolidated,
            'sort_by'                => $sortBy,
            'hide_zero'              => $hideZero,
            'showComment'            => $request->show_comment ?? 'line_comment', // Default: line comment
            'show_lawan'             => $request->has('show_lawan') ? filter_var($request->input('show_lawan'), FILTER_VALIDATE_BOOLEAN) : false,
            'accountDeptBreakdowns'  => $accountDeptBreakdowns,
            'kpiSummary'             => $kpiSummary,
        ];
    }

    public function bukuBesarReport(Request $request)
    {
        return view('buku_besar.buku_besar_report', $this->getBukuBesarData($request));
    }
    public function export(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $data = $this->getBukuBesarData($request);

        // Format tanggal untuk filename (misalnya 2024-12-31 → 31-12-2024)
        $start = \Carbon\Carbon::parse($data['start_date'])->format('d-m-Y');
        $end   = \Carbon\Carbon::parse($data['end_date'])->format('d-m-Y');

        $filename = "buku_besar_{$start}_sd_{$end}";

        if ($request->format === 'excel') {
            return Excel::download(
                new BukuBesarExport(
                    $data['rows'],
                    $data['saldoAwalPerAkun'],
                    $data['groupedByAccount'],
                    $data['totalByType'],
                    $data['start_date'],
                    $data['end_date'],
                    $data['departemen_name'] ?? 'Semua Departemen (Konsolidasi)',
                    $data['selected_departemen_id'] ?? null,
                    $data['showComment'] ?? 'line_comment',
                    $data['accounts'] ?? null,
                    $data['show_lawan'] ?? false
                ),
                $filename . '.xlsx'
            );
        } elseif ($request->format === 'pdf') {
            $pdf = Pdf::loadView('buku_besar.export_pdf', $data)->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        return back();
    }

    /**
     * Inline edit: ganti akun lawan transaksi dari baris Buku Besar.
     * Body: { new_kode_akun: string }
     * Logika: temukan sibling contra detail (satu yang ada di sisi berlawanan),
     * lalu ganti kode_akunnya. Hanya berlaku jika single-contra (1 sibling).
     */
    public function updateContra(Request $request, $id)
    {
        $request->validate(['new_kode_akun' => 'required|string|exists:chart_of_accounts,kode_akun']);

        $detail = JournalEntryDetail::findOrFail($id);
        $isDebit = (float) $detail->debits > 0;

        // Cari sibling contra (sisi berlawanan)
        $siblings = JournalEntryDetail::where('journal_entry_id', $detail->journal_entry_id)
            ->where('id', '!=', $detail->id)
            ->get();

        $contraLines = $siblings->filter(function ($s) use ($isDebit) {
            return $isDebit ? (float) $s->credits > 0 : (float) $s->debits > 0;
        });

        // Fallback: semua sibling jika tidak ada sisi berlawanan
        if ($contraLines->isEmpty()) {
            $contraLines = $siblings;
        }

        if ($contraLines->count() !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Edit inline hanya tersedia untuk transaksi dengan tepat 1 lawan akun.',
            ], 422);
        }

        $contraLine = $contraLines->first();
        $contraLine->update(['kode_akun' => $request->new_kode_akun]);

        // Ambil nama akun baru untuk response
        $namaAkun = \App\ChartOfAccount::where('kode_akun', $request->new_kode_akun)->value('nama_akun');

        return response()->json([
            'success'   => true,
            'message'   => 'Lawan transaksi berhasil diubah.',
            'kode_akun' => $request->new_kode_akun,
            'nama_akun' => $namaAkun,
        ]);
    }

    /**
     * Inline edit: ganti departemen_akun_id dari baris JournalEntryDetail.
     * Body: { departemen_akun_id: int|null }
     */
    public function updateDepartemen(Request $request, $id)
    {
        $request->validate([
            'departemen_akun_id' => 'nullable|integer|exists:departemen_akuns,id',
        ]);

        $detail = JournalEntryDetail::findOrFail($id);
        $detail->update(['departemen_akun_id' => $request->departemen_akun_id ?: null]);

        $dept = null;
        if ($detail->departemen_akun_id) {
            $deptAkun = \App\DepartemenAkun::with('departemen')->find($detail->departemen_akun_id);
            $dept = $deptAkun ? optional($deptAkun->departemen)->only(['id', 'kode', 'deskripsi']) : null;
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Departemen berhasil diubah.',
            'departemen' => $dept,
        ]);
    }

    /**
     * Bulk edit: ganti lawan transaksi untuk banyak baris JournalEntryDetail terpilih.
     * Body: { detail_ids: array, new_kode_akun: string }
     * Memperbarui lawan transaksi untuk transaksi yang single-contra,
     * serta melewati transaksi yang multi-contra.
     */
    public function bulkUpdateContra(Request $request)
    {
        $request->validate([
            'detail_ids'    => 'required|array|min:1',
            'detail_ids.*'  => 'integer|exists:journal_entry_details,id',
            'new_kode_akun' => 'required|string|exists:chart_of_accounts,kode_akun',
        ]);

        $newKodeAkun = $request->new_kode_akun;
        $detailIds = $request->detail_ids;

        $details = JournalEntryDetail::whereIn('id', $detailIds)->get();
        $journalEntryIds = $details->pluck('journal_entry_id')->filter()->unique();

        $voucherDetails = JournalEntryDetail::whereIn('journal_entry_id', $journalEntryIds)
            ->get()
            ->groupBy('journal_entry_id');

        $updatedDetailIds = [];
        $skippedCount = 0;
        $contraLinesToUpdate = [];

        foreach ($details as $detail) {
            $siblings = $voucherDetails->get($detail->journal_entry_id, collect())
                ->where('id', '!=', $detail->id);

            $isDebit = (float) $detail->debits > 0;
            $contraLines = $siblings->filter(function ($s) use ($isDebit) {
                return $isDebit ? (float) $s->credits > 0 : (float) $s->debits > 0;
            });

            if ($contraLines->isEmpty()) {
                $contraLines = $siblings;
            }

            if ($contraLines->count() === 1) {
                $contraLine = $contraLines->first();
                $contraLinesToUpdate[$contraLine->id] = $contraLine;
                $updatedDetailIds[] = $detail->id;
            } else {
                $skippedCount++;
            }
        }

        if (!empty($contraLinesToUpdate)) {
            JournalEntryDetail::whereIn('id', array_keys($contraLinesToUpdate))
                ->update(['kode_akun' => $newKodeAkun]);
        }

        $namaAkun = \App\ChartOfAccount::where('kode_akun', $newKodeAkun)->value('nama_akun');
        $successCount = count($updatedDetailIds);

        $msg = "Lawan transaksi berhasil diubah untuk {$successCount} transaksi.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} transaksi multi-contra dilewati).";
        }

        return response()->json([
            'success'       => true,
            'message'       => $msg,
            'updated_count' => $successCount,
            'skipped_count' => $skippedCount,
            'updated_ids'   => $updatedDetailIds,
            'kode_akun'     => $newKodeAkun,
            'nama_akun'     => $namaAkun,
        ]);
    }

    /**
     * Bulk edit: ganti departemen_akun_id untuk banyak baris JournalEntryDetail terpilih.
     * Body: { detail_ids: array, departemen_akun_id: int|null }
     */
    public function bulkUpdateDepartemen(Request $request)
    {
        $request->validate([
            'detail_ids'         => 'required|array|min:1',
            'detail_ids.*'       => 'integer|exists:journal_entry_details,id',
            'departemen_akun_id' => 'nullable|integer|exists:departemen_akuns,id',
        ]);

        $detailIds = $request->detail_ids;
        $deptAkunId = $request->departemen_akun_id ?: null;

        $updatedCount = JournalEntryDetail::whereIn('id', $detailIds)
            ->update(['departemen_akun_id' => $deptAkunId]);

        $dept = null;
        if ($deptAkunId) {
            $deptAkun = \App\DepartemenAkun::with('departemen')->find($deptAkunId);
            $dept = $deptAkun ? optional($deptAkun->departemen)->only(['id', 'kode', 'deskripsi']) : null;
        }

        return response()->json([
            'success'       => true,
            'message'       => "Departemen berhasil diubah untuk {$updatedCount} baris transaksi.",
            'updated_count' => $updatedCount,
            'updated_ids'   => $detailIds,
            'departemen'    => $dept,
        ]);
    }
}
