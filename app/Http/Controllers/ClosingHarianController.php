<?php

namespace App\Http\Controllers;

use App\ClosingHarian;
use App\ClosingHarianDetail;
use App\JournalEntryDetail;
use App\linkedAccounts;
use App\UnitKerja;
use App\Wahana;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;

class ClosingHarianController extends Controller
{
    //
    public function index()
    {
        $data = ClosingHarian::with(['unitKerja'])->orderBy('tanggal')->get();
        return view('closing_harian.index', compact('data'));
    }
    public function create()
    {
        $wahana = Wahana::all();
        $unitKerja = UnitKerja::all();
        $piutangAccount = \App\linkedAccounts::with('akun', 'departemen')
            ->where('modul', 'closing')
            ->where('kode', 'Piutang Qris')
            ->first();
        $kasOmsetAccount = \App\linkedAccounts::with('akun', 'departemen')
            ->where('modul', 'closing')
            ->where('kode', 'Kas Omset')
            ->first();
        $titipanOmsetAccount = \App\linkedAccounts::with('akun', 'departemen')
            ->where('modul', 'closing')
            ->where('kode', 'Titipan Omset Merchandise')
            ->first();
        $bebanSewaAccount = \App\linkedAccounts::with('akun', 'departemen')
            ->where('modul', 'closing')
            ->where('kode', 'Beban Bagi Hasil (Sewa)')
            ->first();

        $pendapatanOpAccount = \App\linkedAccounts::with('akun', 'departemen')
            ->where('modul', 'closing')
            ->where('kode', 'Pendapatan Non Operasional Lainnya')
            ->first();

        return view('closing_harian.create', compact('wahana', 'unitKerja', 'piutangAccount', 'kasOmsetAccount', 'titipanOmsetAccount', 'bebanSewaAccount', 'pendapatanOpAccount'));
    }
    private function parseNumber($value)
    {
        if (is_null($value)) return 0;
        $clean = str_replace(['.', ','], ['', '.'], $value);
        return (float)$clean;
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jumlah_pengunjung' => 'nullable|integer',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'details' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Buat header
            $closing = ClosingHarian::create([
                'tanggal'           => $request->tanggal,
                'jumlah_pengunjung' => $request->jumlah_pengunjung,
                'unit_kerja_id'     => $request->unit_kerja_id,
            ]);

            // dump("Closing header tabel", $closing->toArray());

            $unitId = $request->unit_kerja_id;
            $unitKerja = unitKerja::find($unitId);
            $unitCode = $unitKerja->kode_unit;
            $unitName = $unitKerja->nama_unit;

            // 2️⃣ Ambil akun-akun yang terhubung
            $piutangQrisAccount = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Piutang Qris')->where('unit_kerja_id', $unitId)->first();
            $kasOmset = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Kas Omset')->where('unit_kerja_id', $unitId)->first();
            $titipanOmset = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Titipan Omset Merchandise')->where('unit_kerja_id', $unitId)->first();
            $bebanSewa = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Beban Bagi Hasil(Sewa)')->where('unit_kerja_id', $unitId)->first();
            $pendapatanNonOp = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Pendapatan Non Operasional')->where('unit_kerja_id', $unitId)->first();

            // dump([
            //     'piutangQrisAccount' => $piutangQrisAccount,
            //     'kasOmset' => $kasOmset,
            //     'titipanOmset' => $titipanOmset,
            //     'bebanSewa' => $bebanSewa,
            //     'pendapatanNonOp' => $pendapatanNonOp,
            // ]);

            // 3️⃣ Loop detail
            $totalOmset = $totalQris = $totalCash = $totalMerch = $totalRca = $totalTitipan = $totalLebih = 0;



            foreach ($request->details as $row) {
                if (empty($row['items'])) continue;

                $rowOmset = 0;

                foreach ($row['items'] as $item) {
                    $jumlah = $this->parseNumber($item['jumlah'] ?? 0);
                    $rowOmset += $jumlah;

                    $closing_detail = ClosingHarianDetail::create([
                        'closing_harian_id' => $closing->id,
                        'wahana_item_id'    => $item['item_id'],
                        'qty'               => $this->parseNumber($item['qty'] ?? 0),
                        'harga'             => $this->parseNumber($item['harga'] ?? 0),
                        'jumlah'            => $jumlah,
                        'omset_total'       => $this->parseNumber($row['omset_total'] ?? $rowOmset),
                        'qris'              => $this->parseNumber($row['qris'] ?? 0),
                        'cash'              => $this->parseNumber($row['cash'] ?? 0),
                        'merch'             => $this->parseNumber($row['merch'] ?? 0),
                        'rca'               => $this->parseNumber($row['rca'] ?? 0),
                        'titipan'           => $this->parseNumber($row['titipan'] ?? 0),
                        'lebih_kurang'      => $this->parseNumber($row['lebih_kurang'] ?? 0),
                    ]);

                    // dump("Closing detail header tabel", $closing_detail->toArray());
                }

                // Agregasi ke header
                $totalOmset  += $this->parseNumber($row['omset_total'] ?? $rowOmset);
                $totalQris   += $this->parseNumber($row['qris'] ?? 0);
                $totalCash   += $this->parseNumber($row['cash'] ?? 0);
                $totalMerch  += $this->parseNumber($row['merch'] ?? 0);
                $totalRca    += $this->parseNumber($row['rca'] ?? 0);
                $totalTitipan += $this->parseNumber($row['titipan'] ?? 0);
                $totalLebih  += $this->parseNumber($row['lebih_kurang'] ?? 0);
            }

            $mdrAmount = $totalTitipan * 0.007;
            $setorTitipanPenjualan = $totalTitipan - $mdrAmount;
            $subtotalAfterMdr = $totalTitipan - $mdrAmount;

            $closing->update([
                'total_omset'        => $totalOmset,
                'total_qris'         => $totalQris,
                'total_cash'         => $totalCash,
                'total_merch'        => $totalMerch,
                'total_rca'          => $totalRca,
                'total_titipan'      => $totalTitipan,
                'total_lebih_kurang' => $totalLebih,
                'mdr_amount'         => $mdrAmount,
                'subtotal_after_mdr' => $subtotalAfterMdr,
            ]);

            // 4️⃣ Buat Journal Header
            $source = "CH-$unitCode-$request->tanggal";
            $journal = \App\JournalEntry::create([
                'source'  => $source,
                'tanggal' => $request->tanggal,
                'comment' => "",
            ]);

            // dump("Journal Header", $journal->toArray());
            $journalEntries = [];

            // 5️⃣ Generate Journal Entries
            if ($totalQris > 0 && $piutangQrisAccount) {
                $journalEntries[] = [
                    'account_code' => $piutangQrisAccount->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $piutangQrisAccount->akun_id)
                        ->where('departemen_id', $piutangQrisAccount->departemen_id)
                        ->value('id'),
                    'debit' => $totalQris,
                    'credit' => 0,
                    'comment' => "Penjualan $unitName dibayarkan via QRIS",
                    'status' => 2
                ];
            }

            if ($totalCash > 0 && $kasOmset) {
                $journalEntries[] = [
                    'account_code' => $kasOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $kasOmset->akun_id)
                        ->where('departemen_id', $kasOmset->departemen_id)
                        ->value('id'),
                    'debit' => $totalCash,
                    'credit' => 0,
                    'comment' => "Penjualan $unitName diterima Cash",
                    'status' => 2
                ];
            }
            if ($totalTitipan > 0 && $titipanOmset) {
                $journalEntries[] = [
                    'account_code' => $titipanOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $titipanOmset->akun_id)
                        ->where('departemen_id', $titipanOmset->departemen_id)
                        ->value('id'),
                    'debit' => 0,
                    'credit' => $totalTitipan,
                    'comment' => "Titipan Penjualan Merchandise $unitName",
                    'status' => 2
                ];
            }

            foreach ($closing->details as $detail) {
                $acc = $detail->wahanaItem->account ?? null;
                if ($acc && $detail->jumlah > 0) {
                    $journalEntries[] = [
                        'account_code' => $acc->kode_akun,
                        'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $acc->id)
                            ->where('departemen_id', $detail->wahanaItem->departemen_id ?? null)
                            ->value('id'),
                        'debit' => 0,
                        'credit' => $detail->jumlah,
                        'comment' => "Penjualan {$detail->wahanaItem->nama_item} - $unitName",
                        'status' => 2
                    ];
                }
            }

            if ($totalTitipan > 0 && $titipanOmset) {
                $journalEntries[] = [
                    'account_code' => $titipanOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $titipanOmset->akun_id)
                        ->where('departemen_id', $titipanOmset->departemen_id)
                        ->value('id'),
                    'debit' => $totalTitipan,
                    'credit' => 0,
                    'comment' => "Setor Titipan Penjualan Merchandise $unitName",
                    'status' => 2
                ];
            }

            if ($mdrAmount > 0 && $pendapatanNonOp) {
                $journalEntries[] = [
                    'account_code' => $pendapatanNonOp->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $pendapatanNonOp->akun_id)
                        ->where('departemen_id', $pendapatanNonOp->departemen_id)
                        ->value('id'),
                    'debit' => 0,
                    'credit' => $mdrAmount,
                    'comment' => "MDR 0,7% Setor Titipan Penjualan Merchandise $unitName",
                    'status' => 2
                ];
            }
            if ($setorTitipanPenjualan > 0 && $kasOmset) {
                $journalEntries[] = [
                    'account_code' => $kasOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $kasOmset->akun_id)
                        ->where('departemen_id', $kasOmset->departemen_id)
                        ->value('id'),
                    'debit' => 0,
                    'credit' => $setorTitipanPenjualan,
                    'comment' => "Setor Titipan Penjualan Merchandise $unitName dikurangi MDR",
                    'status' => 2
                ];
            }
            if ($totalMerch > 0 && $bebanSewa) {
                $departAkunBeban = \App\DepartemenAkun::where('akun_id', $bebanSewa->akun_id)
                    ->where('departemen_id', $bebanSewa->departemen_id)->value('id');
                $departAkunKas = \App\DepartemenAkun::where('akun_id', $kasOmset->akun_id)
                    ->where('departemen_id', $kasOmset->departemen_id)->value('id');

                $journalEntries[] = [
                    'account_code' => $bebanSewa->akun->kode_akun,
                    'departemen_akun_id' => $departAkunBeban,
                    'debit' => $totalMerch,
                    'credit' => 0,
                    'comment' => "Setor Bagi Hasil Omset $unitName ke Ancol",
                    'status' => 2
                ];
                $journalEntries[] = [
                    'account_code' => $kasOmset->akun->kode_akun,
                    'departemen_akun_id' => $departAkunKas,
                    'debit' => 0,
                    'credit' => $totalMerch,
                    'comment' => "Setor Bagi Hasil Omset $unitName ke Ancol",
                    'status' => 2
                ];
            }

            // 6️⃣ Simpan Journal Entry Details
            $totalDebit = $totalCredit = 0;

            foreach ($journalEntries as $entry) {
                $journal_detail = JournalEntryDetail::create([
                    'journal_entry_id'   => $journal->id,
                    'kode_akun'          => $entry['account_code'],
                    'departemen_akun_id' => $entry['departemen_akun_id'],
                    'debits'              => $entry['debit'],
                    'credits'             => $entry['credit'],
                    'comment'            => $entry['comment'],
                    'status'             => $entry['status'],
                ]);

                $totalDebit  += (float)$entry['debit'];
                $totalCredit += (float)$entry['credit'];
                // dump("Journal Entry Detail", $journal_detail->toArray());s
            }
            $balance = $totalDebit - $totalCredit;
            $isBalanced = abs($balance) < 0.01; // toleransi pembulatan floating point

            // dump([
            //     'SUMMARY JOURNAL ENTRY' => [
            //         'Total Debit'  => number_format($totalDebit, 2, ',', '.'),
            //         'Total Credit' => number_format($totalCredit, 2, ',', '.'),
            //         'Selisih'      => number_format($balance, 2, ',', '.'),
            //         'Status'       => $isBalanced ? '✅ Balanced' : '❌ Tidak Balance',
            //     ]
            // ]);

            DB::commit();

            return redirect()->route('closing_harian.index')
                ->with('success', 'Closing harian berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd('❌ Error:', $e->getMessage(), $e->getTraceAsString(), [
            //     'request_items' => $request->items
            // ]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jumlah_pengunjung' => 'nullable|integer',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'details' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            $closing = ClosingHarian::findOrFail($id);
            $unitId = $request->unit_kerja_id;
            $unitKerja = UnitKerja::find($unitId);
            $unitCode = $unitKerja->kode_unit;
            $unitName = $unitKerja->nama_unit;
            $source = "CH-$unitCode-$request->tanggal";

            $piutangQrisAccount = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Piutang Qris')->where('unit_kerja_id', $unitId)->first();
            $kasOmset = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Kas Omset')->where('unit_kerja_id', $unitId)->first();
            $titipanOmset = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Titipan Omset Merchandise')->where('unit_kerja_id', $unitId)->first();
            $bebanSewa = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Beban Bagi Hasil(Sewa)')->where('unit_kerja_id', $unitId)->first();
            $pendapatanNonOp = linkedAccounts::with(['akun', 'departemen'])
                ->where('kode', 'Pendapatan Non Operasional')->where('unit_kerja_id', $unitId)->first();

            // 🔹 1️⃣ Update header basic info
            $closing->update([
                'tanggal'           => $request->tanggal,
                'jumlah_pengunjung' => $request->jumlah_pengunjung,
                'unit_kerja_id'     => $unitId,
            ]);
            // dump("header closing", $closing->toArray());

            // 🔹 2️⃣ Hapus detail lama lalu insert ulang
            $closing->details()->delete();

            // 🔹 3️⃣ Hapus journal lama berdasarkan source
            $oldJournal = \App\JournalEntry::where('source', $source)->first();
            if ($oldJournal) {
                $oldJournal->details()->delete();
                $oldJournal->delete();
            }

            // 🔹 4️⃣ Re-run seluruh logika perhitungan sama seperti store()
            $totalOmset = $totalQris = $totalCash = $totalMerch = $totalRca = $totalTitipan = $totalLebih = 0;



            foreach ($request->details as $row) {
                if (empty($row['items'])) continue;

                $rowOmset = 0;

                foreach ($row['items'] as $item) {
                    $jumlah = $this->parseNumber($item['jumlah'] ?? 0);
                    $rowOmset += $jumlah;

                    $closing_detail = ClosingHarianDetail::create([
                        'closing_harian_id' => $closing->id,
                        'wahana_item_id'    => $item['item_id'],
                        'qty'               => $this->parseNumber($item['qty'] ?? 0),
                        'harga'             => $this->parseNumber($item['harga'] ?? 0),
                        'jumlah'            => $jumlah,
                        'omset_total'       => $this->parseNumber($row['omset_total'] ?? $rowOmset),
                        'qris'              => $this->parseNumber($row['qris'] ?? 0),
                        'cash'              => $this->parseNumber($row['cash'] ?? 0),
                        'merch'             => $this->parseNumber($row['merch'] ?? 0),
                        'rca'               => $this->parseNumber($row['rca'] ?? 0),
                        'titipan'           => $this->parseNumber($row['titipan'] ?? 0),
                        'lebih_kurang'      => $this->parseNumber($row['lebih_kurang'] ?? 0),
                    ]);
                    // dump("Closing detail header tabel", $closing_detail->toArray());
                }

                $totalOmset  += $this->parseNumber($row['omset_total'] ?? $rowOmset);
                $totalQris   += $this->parseNumber($row['qris'] ?? 0);
                $totalCash   += $this->parseNumber($row['cash'] ?? 0);
                $totalMerch  += $this->parseNumber($row['merch'] ?? 0);
                $totalRca    += $this->parseNumber($row['rca'] ?? 0);
                $totalTitipan += $this->parseNumber($row['titipan'] ?? 0);
                $totalLebih  += $this->parseNumber($row['lebih_kurang'] ?? 0);
            }

            // 🔹 5️⃣ Update total ke header
            $mdrAmount = $totalTitipan * 0.007;
            $setorTitipanPenjualan = $totalTitipan - $mdrAmount;
            $subtotalAfterMdr = $totalTitipan - $mdrAmount;
            $closing->update([
                'total_omset'        => $totalOmset,
                'total_qris'         => $totalQris,
                'total_cash'         => $totalCash,
                'total_merch'        => $totalMerch,
                'total_rca'          => $totalRca,
                'total_titipan'      => $totalTitipan,
                'total_lebih_kurang' => $totalLebih,
                'mdr_amount'         => $mdrAmount,
                'subtotal_after_mdr' => $subtotalAfterMdr,
            ]);

            // 🔹 6️⃣ Recreate journal (copy logic dari store)
            $journal = \App\JournalEntry::create([
                'source'  => $source,
                'tanggal' => $request->tanggal,
                'comment' => "",
            ]);
            // dump("Journal Header", $journal->toArray());


            // --- generate journalEntries[] sama seperti di store() ---
            $journalEntries = []; // isi sama persis dari store()
            if ($totalQris > 0 && $piutangQrisAccount) {
                $journalEntries[] = [
                    'account_code' => $piutangQrisAccount->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $piutangQrisAccount->akun_id)
                        ->where('departemen_id', $piutangQrisAccount->departemen_id)
                        ->value('id'),
                    'debit' => $totalQris,
                    'credit' => 0,
                    'comment' => "Penjualan $unitName dibayarkan via QRIS",
                    'status' => 2
                ];
            }

            if ($totalCash > 0 && $kasOmset) {
                $journalEntries[] = [
                    'account_code' => $kasOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $kasOmset->akun_id)
                        ->where('departemen_id', $kasOmset->departemen_id)
                        ->value('id'),
                    'debit' => $totalCash,
                    'credit' => 0,
                    'comment' => "Penjualan $unitName diterima Cash",
                    'status' => 2
                ];
            }
            if ($totalTitipan > 0 && $titipanOmset) {
                $journalEntries[] = [
                    'account_code' => $titipanOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $titipanOmset->akun_id)
                        ->where('departemen_id', $titipanOmset->departemen_id)
                        ->value('id'),
                    'debit' => 0,
                    'credit' => $totalTitipan,
                    'comment' => "Titipan Penjualan Merchandise $unitName",
                    'status' => 2
                ];
            }

            foreach ($closing->details as $detail) {
                $acc = $detail->wahanaItem->account ?? null;
                if ($acc && $detail->jumlah > 0) {
                    $journalEntries[] = [
                        'account_code' => $acc->kode_akun,
                        'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $acc->id)
                            ->where('departemen_id', $detail->wahanaItem->departemen_id ?? null)
                            ->value('id'),
                        'debit' => 0,
                        'credit' => $detail->jumlah,
                        'comment' => "Penjualan {$detail->wahanaItem->nama_item} - $unitName",
                        'status' => 2
                    ];
                }
            }

            if ($totalTitipan > 0 && $titipanOmset) {
                $journalEntries[] = [
                    'account_code' => $titipanOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $titipanOmset->akun_id)
                        ->where('departemen_id', $titipanOmset->departemen_id)
                        ->value('id'),
                    'debit' => $totalTitipan,
                    'credit' => 0,
                    'comment' => "Setor Titipan Penjualan Merchandise $unitName",
                    'status' => 2
                ];
            }

            if ($mdrAmount > 0 && $pendapatanNonOp) {
                $journalEntries[] = [
                    'account_code' => $pendapatanNonOp->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $pendapatanNonOp->akun_id)
                        ->where('departemen_id', $pendapatanNonOp->departemen_id)
                        ->value('id'),
                    'debit' => 0,
                    'credit' => $mdrAmount,
                    'comment' => "MDR 0,7% Setor Titipan Penjualan Merchandise $unitName",
                    'status' => 2
                ];
            }
            if ($setorTitipanPenjualan > 0 && $kasOmset) {
                $journalEntries[] = [
                    'account_code' => $kasOmset->akun->kode_akun,
                    'departemen_akun_id' => \App\DepartemenAkun::where('akun_id', $kasOmset->akun_id)
                        ->where('departemen_id', $kasOmset->departemen_id)
                        ->value('id'),
                    'debit' => 0,
                    'credit' => $setorTitipanPenjualan,
                    'comment' => "Setor Titipan Penjualan Merchandise $unitName dikurangi MDR",
                    'status' => 2
                ];
            }
            if ($totalMerch > 0 && $bebanSewa) {
                $departAkunBeban = \App\DepartemenAkun::where('akun_id', $bebanSewa->akun_id)
                    ->where('departemen_id', $bebanSewa->departemen_id)->value('id');
                $departAkunKas = \App\DepartemenAkun::where('akun_id', $kasOmset->akun_id)
                    ->where('departemen_id', $kasOmset->departemen_id)->value('id');

                $journalEntries[] = [
                    'account_code' => $bebanSewa->akun->kode_akun,
                    'departemen_akun_id' => $departAkunBeban,
                    'debit' => $totalMerch,
                    'credit' => 0,
                    'comment' => "Setor Bagi Hasil Omset $unitName ke Ancol",
                    'status' => 2
                ];
                $journalEntries[] = [
                    'account_code' => $kasOmset->akun->kode_akun,
                    'departemen_akun_id' => $departAkunKas,
                    'debit' => 0,
                    'credit' => $totalMerch,
                    'comment' => "Setor Bagi Hasil Omset $unitName ke Ancol",
                    'status' => 2
                ];
            }

            // 6️⃣ Simpan Journal Entry Details
            $totalDebit = $totalCredit = 0;

            foreach ($journalEntries as $entry) {
                $journal_detail = JournalEntryDetail::create([
                    'journal_entry_id'   => $journal->id,
                    'kode_akun'          => $entry['account_code'],
                    'departemen_akun_id' => $entry['departemen_akun_id'],
                    'debits'              => $entry['debit'],
                    'credits'             => $entry['credit'],
                    'comment'            => $entry['comment'],
                    'status'             => $entry['status'],
                ]);

                $totalDebit  += (float)$entry['debit'];
                $totalCredit += (float)$entry['credit'];
                // dump("Journal Entry Detail", $journal_detail->toArray());
            }
            $balance = $totalDebit - $totalCredit;
            $isBalanced = abs($balance) < 0.01; // toleransi pembulatan floating point

            // dump([
            //     'SUMMARY JOURNAL ENTRY' => [
            //         'Total Debit'  => number_format($totalDebit, 2, ',', '.'),
            //         'Total Credit' => number_format($totalCredit, 2, ',', '.'),
            //         'Selisih'      => number_format($balance, 2, ',', '.'),
            //         'Status'       => $isBalanced ? '✅ Balanced' : '❌ Tidak Balance',
            //     ]
            // ]);
            DB::commit();

            return redirect()->route('closing_harian.index')
                ->with('success', 'Closing harian berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        $data = ClosingHarian::with(['unitKerja', 'details', 'details.wahanaItem'])->find($id);
        return view('closing_harian.show', compact('data'));
    }
    public function edit($id)
    {
        $data = ClosingHarian::with([
            'unitKerja',
            'details',
            'details.wahanaItem',
            'details.wahanaItem.account',
            'details.wahanaItem.departemen',
            'details.wahanaItem.wahana',
        ])->find($id);
        $unitKerja = UnitKerja::all();
        return view('closing_harian.edit', compact('data', 'unitKerja'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $closing = \App\ClosingHarian::with('unitKerja', 'details', 'details.wahanaItem')->findOrFail($id);
            $tanggal   = $closing->tanggal;
            $unitKerja = $closing->unitKerja;
            $unitCode  = $unitKerja->kode_unit ?? 'UNK';
            $source    = "CH-$unitCode-$tanggal";

            // 2) Hapus Journal Entry
            $journal = \App\JournalEntry::where('source', $source)
                ->first();

            if ($journal) {
                \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->delete();
                $journal->delete();
            }

            // 3) Hapus detail closing
            \App\ClosingHarianDetail::where('closing_harian_id', $closing->id)->delete();

            // 4) Hapus header invoice
            $closing->delete();

            DB::commit();
            return redirect()->route('closing_harian.index')
                ->with('success', ' Closing harian berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd("❌ ERROR DELETE INVOICE:", $e->getMessage(), $e->getTraceAsString());
            return back()->withErrors('Gagal menghapus closing: ' . $e->getMessage());
        }
    }
    public function print($id)
    {
        $data = ClosingHarian::with([
            'unitKerja',
            'details',
            'details.wahanaItem',
            'details.wahanaItem.account',
            'details.wahanaItem.departemen',
            'details.wahanaItem.wahana',
        ])->find($id);
        $unitKerja = UnitKerja::all();

        return view('closing_harian.print', compact('data', 'unitKerja'));
    }
    public function downloadPdf($id)
    {
        $data = ClosingHarian::with([
            'unitKerja',
            'details',
            'details.wahanaItem',
            'details.wahanaItem.account',
            'details.wahanaItem.departemen',
            'details.wahanaItem.wahana',
        ])->find($id);
        $isPdf = true;
        $pdf = Pdf::loadView('closing_harian.print', compact(
            'data',
            'isPdf'
        ))->setPaper('A4', 'landscape');

        return $pdf->download("{$data->unitKerja->nama_unit}- {$data->tanggal}.pdf");
    }
}
