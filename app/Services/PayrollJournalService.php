<?php

namespace App\Services;

use App\ChartOfAccount;
use App\DepartemenAkun;
use App\Departement;
use App\Employee;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\linkedAccounts;
use App\PembayaranGaji;
use App\PembayaranGajiDetail;
use Illuminate\Support\Collection;

class PayrollJournalService
{
    /**
     * Map Unit Kerja code/name to Department code (0001 - 0009).
     */
    public const UNIT_DEPT_CODE_MAP = [
        'BDG'     => '0001',
        'PUSAT'   => '0001',
        'DFN'     => '0002',
        'DUFAN'   => '0002',
        'ODS'     => '0003',
        'AWA'     => '0004',
        'JBL'     => '0005',
        'PBN'     => '0006',
        'PBS'     => '0006',
        'SQP'     => '0007',
        'GOWET'   => '0008',
        'GWT'     => '0008',
        'EMO'     => '0009',
    ];

    /**
     * Default COA code for Payroll Expense per KamusIndikatorAkunSeeder.
     */
    public const DEFAULT_PAYROLL_EXPENSE_COA = '5301001';

    /**
     * Default COA code for Cash / Payroll Payable fallback.
     */
    public const DEFAULT_CASH_COA = '1101000';

    /**
     * Create or update balanced General Ledger JournalEntry for a PembayaranGaji record.
     */
    public function syncJournalForPayroll(PembayaranGaji $pembayaran): ?JournalEntry
    {
        $pembayaran->loadMissing(['employee.unitKerja', 'details.komponen']);
        $employee = $pembayaran->employee;

        $totalGross = 0.0;
        $totalDeduction = 0.0;

        foreach ($pembayaran->details as $detail) {
            $hari = (float) ($detail->jumlah_hari ?? 0);
            $nilai = (float) ($detail->nilai ?? 0);
            $potongan = (float) ($detail->potongan ?? 0);

            $totalGross += ($nilai * $hari);
            $totalDeduction += ($potongan * $hari);
        }

        $netPayable = max(0.0, $totalGross - $totalDeduction);
        $source = 'PAYROLL-' . $pembayaran->id;

        // Hapus jurnal lama jika ada (saat update)
        $existingJournal = JournalEntry::where('source', $source)->first();
        if ($existingJournal) {
            $existingJournal->details()->delete();
            $existingJournal->delete();
        }

        if ($netPayable <= 0) {
            return null;
        }

        $departemenId = $this->resolveDepartemenId($employee);
        $expenseCoa = $this->resolvePayrollExpenseAccount();
        $creditCoa = $this->resolvePayrollCreditAccount($employee);

        if (!$expenseCoa || !$creditCoa) {
            return null;
        }

        $expenseDeptAkunId = $this->ensureDepartemenAkun($expenseCoa->id, $departemenId);
        $creditDeptAkunId = $this->ensureDepartemenAkun($creditCoa->id, $departemenId);

        $empName = $employee ? $employee->nama_karyawan : ('Karyawan #' . $pembayaran->kode_karyawan);
        $unitName = optional(optional($employee)->unitKerja)->nama_unit ?? 'Pusat';

        $journal = JournalEntry::create([
            'source'  => $source,
            'tanggal' => $pembayaran->tanggal_pembayaran,
            'comment' => "Pembayaran Gaji {$empName} ({$unitName}) Periode {$pembayaran->periode_awal} s/d {$pembayaran->periode_akhir}",
        ]);

        // 1. Debit Beban Gaji (dengan alokasi departemen wajib sesuai CLAUDE.md & AGENTS.md)
        JournalEntryDetail::create([
            'journal_entry_id'   => $journal->id,
            'kode_akun'          => $expenseCoa->kode_akun,
            'departemen_akun_id' => $expenseDeptAkunId,
            'debits'             => $netPayable,
            'credits'            => 0,
            'status'             => 2,
            'comment'            => "Gaji Karyawan {$empName} ({$unitName})",
        ]);

        // 2. Credit Kas / Bank / Hutang Gaji
        JournalEntryDetail::create([
            'journal_entry_id'   => $journal->id,
            'kode_akun'          => $creditCoa->kode_akun,
            'departemen_akun_id' => $creditDeptAkunId,
            'debits'             => 0,
            'credits'            => $netPayable,
            'status'             => 2,
            'comment'            => "Pencairan Gaji Karyawan {$empName}",
        ]);

        return $journal;
    }

    /**
     * Delete associated GL JournalEntry when a PembayaranGaji record is deleted.
     */
    public function deleteJournalForPayroll(int $pembayaranId): void
    {
        $source = 'PAYROLL-' . $pembayaranId;
        $journal = JournalEntry::where('source', $source)->first();
        if ($journal) {
            $journal->details()->delete();
            $journal->delete();
        }
    }

    /**
     * Resolve Departement ID from Employee's UnitKerja.
     */
    public function resolveDepartemenId(?Employee $employee): ?int
    {
        if (!$employee) {
            return Departement::where('kode', '0001')->value('id') ?? Departement::value('id');
        }

        if ($employee->unit_kerja_id) {
            $linkedDeptId = linkedAccounts::where('unit_kerja_id', $employee->unit_kerja_id)
                ->whereNotNull('departemen_id')
                ->value('departemen_id');
            if ($linkedDeptId) {
                return (int) $linkedDeptId;
            }

            $unitCode = strtoupper(trim(optional($employee->unitKerja)->kode_unit ?? ''));
            if (isset(self::UNIT_DEPT_CODE_MAP[$unitCode])) {
                $deptId = Departement::where('kode', self::UNIT_DEPT_CODE_MAP[$unitCode])->value('id');
                if ($deptId) {
                    return (int) $deptId;
                }
            }
        }

        return Departement::where('kode', '0001')->value('id') ?? Departement::value('id');
    }

    protected function resolvePayrollExpenseAccount(): ?ChartOfAccount
    {
        return ChartOfAccount::where('kode_akun', self::DEFAULT_PAYROLL_EXPENSE_COA)->first()
            ?? ChartOfAccount::where('kode_akun', 'like', '5301%')->orderBy('kode_akun')->first()
            ?? ChartOfAccount::where('tipe_akun', 'Beban')->orderBy('kode_akun')->first();
    }

    protected function resolvePayrollCreditAccount(?Employee $employee): ?ChartOfAccount
    {
        if ($employee && $employee->unit_kerja_id) {
            $linkedKas = linkedAccounts::with('akun')
                ->where('unit_kerja_id', $employee->unit_kerja_id)
                ->where('kode', 'Kas Omset')
                ->first();
            if ($linkedKas && $linkedKas->akun) {
                return $linkedKas->akun;
            }
        }

        $principalBank = linkedAccounts::with('akun')
            ->where('kode', 'Principal Bank Account')
            ->first();
        if ($principalBank && $principalBank->akun) {
            return $principalBank->akun;
        }

        return ChartOfAccount::where('kode_akun', self::DEFAULT_CASH_COA)->first()
            ?? ChartOfAccount::where('kode_akun', 'like', '1101%')->orderBy('kode_akun')->first()
            ?? ChartOfAccount::where('tipe_akun', 'Aset')->orderBy('kode_akun')->first();
    }

    protected function ensureDepartemenAkun(?int $akunId, ?int $departemenId): ?int
    {
        if (!$akunId || !$departemenId) {
            return null;
        }

        $da = DepartemenAkun::firstOrCreate([
            'akun_id'       => $akunId,
            'departemen_id' => $departemenId,
        ]);

        return $da->id;
    }
}
