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
            'journal_entries.tanggal'
        )
            ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
            ->with(['chartOfAccount', 'journalEntry', 'departemenAkun'])
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

        $rows = $rowsQuery->orderBy('journal_entry_details.kode_akun', 'asc')
            ->orderBy('journal_entries.tanggal', 'asc')
            ->get();

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

        // 3. Grouping by account
        $groupedByAccount = $rows->groupBy(function ($item) {
            return $item->chartOfAccount->nama_akun ?? 'Tanpa Akun';
        });

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
            'showComment'            => $request->show_comment ?? 'line_comment', // Default: line comment
        ];
    }

    public function bukuBesarReport(Request $request)
    {
        return view('buku_besar.buku_besar_report', $this->getBukuBesarData($request));
    }
    public function export(Request $request)
    {
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
                    $data['departemen_name'] ?? 'Semua Departemen (Konsolidasi)'
                ),
                $filename . '.xlsx'
            );
        } elseif ($request->format === 'pdf') {
            $pdf = Pdf::loadView('buku_besar.export_pdf', $data)->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        return back();
    }
}
