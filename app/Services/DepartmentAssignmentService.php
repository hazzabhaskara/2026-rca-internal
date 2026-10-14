<?php

namespace App\Services;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\DepartmentAssignmentLog;
use App\JournalEntryDetail;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DepartmentAssignmentService
{
    /**
     * Determine whether an accounting period for the given date is closed.
     *
     * An accounting period is closed if:
     * 1. There is an entry in start_new_years for the year with status = 'Closing', OR
     * 2. The date falls on or prior to the akhir_periode of any closed period.
     *
     * @param string $date (YYYY-MM-DD or parseable date string)
     * @return bool
     */
    public function isPeriodClosed(string $date): bool
    {
        $formattedDate = date('Y-m-d', strtotime($date));
        $year = (int) date('Y', strtotime($date));

        return DB::table('start_new_years')
            ->where('status', 'Closing')
            ->where(function ($query) use ($year, $formattedDate) {
                $query->where('tahun', $year)
                      ->orWhere('akhir_periode', '>=', $formattedDate);
            })
            ->exists();
    }

    /**
     * Reassign a single journal entry detail to a new department.
     *
     * @param int $detailId
     * @param int $newDepartemenId
     * @param string $reason
     * @param User|null $user
     * @param bool $overrideClosed
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws AuthorizationException
     */
    public function reassign(
        int $detailId,
        int $newDepartemenId,
        string $reason,
        ?User $user = null,
        bool $overrideClosed = false
    ): array {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('Alasan penyesuaian (reason) wajib diisi.');
        }

        $detail = JournalEntryDetail::with(['journalEntry', 'departemenAkun.departemen'])->findOrFail($detailId);
        $entry = $detail->journalEntry;
        $entryDate = $entry ? $entry->tanggal : date('Y-m-d');

        $isClosed = $this->validateClosedPeriod($entryDate, $user, $overrideClosed, "Detail #{$detailId}");

        $dept = Departement::findOrFail($newDepartemenId);
        $account = ChartOfAccount::where('kode_akun', $detail->kode_akun)->firstOrFail();

        $oldDepartemenAkunId = $detail->departemen_akun_id;
        $oldDepartemenId = $detail->departemenAkun ? $detail->departemenAkun->departemen_id : null;

        return DB::transaction(function () use (
            $detail,
            $dept,
            $account,
            $entry,
            $entryDate,
            $oldDepartemenId,
            $oldDepartemenAkunId,
            $reason,
            $user,
            $isClosed,
            $overrideClosed
        ) {
            // Find or create pivot link
            $newDa = DepartemenAkun::firstOrCreate([
                'akun_id'       => $account->id,
                'departemen_id' => $dept->id,
            ]);

            // Synchronize detail allocation in-place
            $detail->departemen_akun_id = $newDa->id;
            $detail->save();

            $actionType = $oldDepartemenAkunId ? 'reassign' : 'initial_assign';

            $log = DepartmentAssignmentLog::create([
                'journal_entry_detail_id' => $detail->id,
                'journal_entry_id'        => $entry ? $entry->id : null,
                'voucher'                 => $entry ? $entry->source : null,
                'kode_akun'               => $detail->kode_akun,
                'old_departemen_id'       => $oldDepartemenId,
                'new_departemen_id'       => $dept->id,
                'old_departemen_akun_id'  => $oldDepartemenAkunId,
                'new_departemen_akun_id'  => $newDa->id,
                'action_type'             => $actionType,
                'reason'                  => $reason,
                'user_id'                 => $user ? $user->id : null,
                'batch_id'                => null,
                'is_override_closed'      => $isClosed && $overrideClosed,
                'ip_address'              => request() ? request()->ip() : null,
            ]);

            if ($isClosed && $overrideClosed) {
                $year = (int) date('Y', strtotime($entryDate));
                $this->triggerRecalculateClosingIfNecessary($year);
            }

            return [
                'success'            => true,
                'action'             => $actionType,
                'detail_id'          => $detail->id,
                'old_departemen_id'  => $oldDepartemenId,
                'new_departemen_id'  => $dept->id,
                'departemen_nama'    => $dept->deskripsi,
                'departemen_kode'    => $dept->kode,
                'departemen_akun_id' => $newDa->id,
                'log_id'             => $log->id,
                'is_override_closed' => $isClosed && $overrideClosed,
                'message'            => "Detail transaksi #{$detail->id} ({$detail->kode_akun}) berhasil dialihkan ke {$dept->deskripsi}.",
            ];
        });
    }

    /**
     * Unassign / detach a single journal entry detail from its department.
     *
     * @param int $detailId
     * @param string $reason
     * @param User|null $user
     * @param bool $overrideClosed
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws AuthorizationException
     */
    public function unassign(
        int $detailId,
        string $reason,
        ?User $user = null,
        bool $overrideClosed = false
    ): array {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('Alasan pelepasan (unassign reason) wajib diisi.');
        }

        $detail = JournalEntryDetail::with(['journalEntry', 'departemenAkun.departemen'])->findOrFail($detailId);
        $entry = $detail->journalEntry;
        $entryDate = $entry ? $entry->tanggal : date('Y-m-d');

        $isClosed = $this->validateClosedPeriod($entryDate, $user, $overrideClosed, "Detail #{$detailId}");

        $oldDepartemenAkunId = $detail->departemen_akun_id;
        $oldDepartemenId = $detail->departemenAkun ? $detail->departemenAkun->departemen_id : null;
        $oldDepartemenNama = $detail->departemenAkun && $detail->departemenAkun->departemen
            ? $detail->departemenAkun->departemen->deskripsi
            : 'Departemen';

        return DB::transaction(function () use (
            $detail,
            $entry,
            $entryDate,
            $oldDepartemenId,
            $oldDepartemenAkunId,
            $oldDepartemenNama,
            $reason,
            $user,
            $isClosed,
            $overrideClosed
        ) {
            // Detach department
            $detail->departemen_akun_id = null;
            $detail->save();

            $log = DepartmentAssignmentLog::create([
                'journal_entry_detail_id' => $detail->id,
                'journal_entry_id'        => $entry ? $entry->id : null,
                'voucher'                 => $entry ? $entry->source : null,
                'kode_akun'               => $detail->kode_akun,
                'old_departemen_id'       => $oldDepartemenId,
                'new_departemen_id'       => null,
                'old_departemen_akun_id'  => $oldDepartemenAkunId,
                'new_departemen_akun_id'  => null,
                'action_type'             => 'unassign',
                'reason'                  => $reason,
                'user_id'                 => $user ? $user->id : null,
                'batch_id'                => null,
                'is_override_closed'      => $isClosed && $overrideClosed,
                'ip_address'              => request() ? request()->ip() : null,
            ]);

            if ($isClosed && $overrideClosed) {
                $year = (int) date('Y', strtotime($entryDate));
                $this->triggerRecalculateClosingIfNecessary($year);
            }

            return [
                'success'                => true,
                'action'                 => 'unassign',
                'detail_id'              => $detail->id,
                'old_departemen_id'      => $oldDepartemenId,
                'new_departemen_id'      => null,
                'old_departemen_akun_id' => $oldDepartemenAkunId,
                'log_id'                 => $log->id,
                'is_override_closed'     => $isClosed && $overrideClosed,
                'message'                => "Detail transaksi #{$detail->id} ({$detail->kode_akun}) berhasil dilepas dari {$oldDepartemenNama}.",
            ];
        });
    }

    /**
     * Bulk Reassign multiple journal entry details to a new department atomically.
     *
     * @param array $detailIds
     * @param int $newDepartemenId
     * @param string $reason
     * @param User|null $user
     * @param bool $overrideClosed
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws AuthorizationException
     */
    public function reassignBulk(
        array $detailIds,
        int $newDepartemenId,
        string $reason,
        ?User $user = null,
        bool $overrideClosed = false
    ): array {
        if (empty($detailIds)) {
            throw new \InvalidArgumentException('Daftar ID transaksi tidak boleh kosong.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('Alasan penyesuaian masal (bulk reason) wajib diisi.');
        }

        $dept = Departement::findOrFail($newDepartemenId);

        $batchId = (string) Str::uuid();

        return DB::transaction(function () use (
            $detailIds,
            $dept,
            $reason,
            $user,
            $overrideClosed,
            $batchId
        ) {
            $accountCache = [];
            $daCache = [];
            $updatedIds = [];
            $logIds = [];
            $affectedYears = [];

            foreach ($detailIds as $detailId) {
                $detail = JournalEntryDetail::with(['journalEntry', 'departemenAkun'])->findOrFail($detailId);
                $entry = $detail->journalEntry;
                $entryDate = $entry ? $entry->tanggal : date('Y-m-d');
                $isClosed = $this->validateClosedPeriod($entryDate, $user, $overrideClosed, "Detail #{$detail->id}");

                if (!isset($accountCache[$detail->kode_akun])) {
                    $account = ChartOfAccount::where('kode_akun', $detail->kode_akun)->firstOrFail();
                    $accountCache[$detail->kode_akun] = $account;
                } else {
                    $account = $accountCache[$detail->kode_akun];
                }

                if (!isset($daCache[$account->id])) {
                    $da = DepartemenAkun::firstOrCreate([
                        'akun_id'       => $account->id,
                        'departemen_id' => $dept->id,
                    ]);
                    $daCache[$account->id] = $da->id;
                }
                $newDaId = $daCache[$account->id];

                $oldDaId = $detail->departemen_akun_id;
                $oldDeptId = $detail->departemenAkun ? $detail->departemenAkun->departemen_id : null;

                if ($isClosed && $overrideClosed && $entry) {
                    $affectedYears[(int) date('Y', strtotime($entryDate))] = true;
                }

                $detail->departemen_akun_id = $newDaId;
                $detail->save();

                $actionType = $oldDaId ? 'reassign' : 'initial_assign';

                $log = DepartmentAssignmentLog::create([
                    'journal_entry_detail_id' => $detail->id,
                    'journal_entry_id'        => $entry ? $entry->id : null,
                    'voucher'                 => $entry ? $entry->source : null,
                    'kode_akun'               => $detail->kode_akun,
                    'old_departemen_id'       => $oldDeptId,
                    'new_departemen_id'       => $dept->id,
                    'old_departemen_akun_id'  => $oldDaId,
                    'new_departemen_akun_id'  => $newDaId,
                    'action_type'             => $actionType,
                    'reason'                  => $reason,
                    'user_id'                 => $user ? $user->id : null,
                    'batch_id'                => $batchId,
                    'is_override_closed'      => $isClosed && $overrideClosed,
                    'ip_address'              => request() ? request()->ip() : null,
                ]);

                $updatedIds[] = $detail->id;
                $logIds[] = $log->id;
            }

            if (!empty($affectedYears)) {
                foreach (array_keys($affectedYears) as $year) {
                    $this->triggerRecalculateClosingIfNecessary($year);
                }
            }

            return [
                'success'           => true,
                'action'            => 'reassign_bulk',
                'updated_count'     => count($updatedIds),
                'reassigned_count'  => count($updatedIds),
                'departemen_id'     => $dept->id,
                'departemen_nama'   => $dept->deskripsi,
                'departemen_kode'   => $dept->kode,
                'batch_id'          => $batchId,
                'log_ids'           => $logIds,
                'message'           => "Sebanyak " . count($updatedIds) . " transaksi berhasil dialihkan ke {$dept->deskripsi}.",
            ];
        });
    }

    /**
     * Bulk Unassign multiple journal entry details atomically.
     *
     * @param array $detailIds
     * @param string $reason
     * @param User|null $user
     * @param bool $overrideClosed
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws AuthorizationException
     */
    public function unassignBulk(
        array $detailIds,
        string $reason,
        ?User $user = null,
        bool $overrideClosed = false
    ): array {
        if (empty($detailIds)) {
            throw new \InvalidArgumentException('Daftar ID transaksi tidak boleh kosong.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('Alasan pelepasan masal (bulk reason) wajib diisi.');
        }

        $batchId = (string) Str::uuid();

        return DB::transaction(function () use (
            $detailIds,
            $reason,
            $user,
            $overrideClosed,
            $batchId
        ) {
            $unassignedIds = [];
            $logIds = [];
            $affectedYears = [];

            foreach ($detailIds as $detailId) {
                $detail = JournalEntryDetail::with(['journalEntry', 'departemenAkun'])->findOrFail($detailId);
                $entry = $detail->journalEntry;
                $entryDate = $entry ? $entry->tanggal : date('Y-m-d');
                $isClosed = $this->validateClosedPeriod($entryDate, $user, $overrideClosed, "Detail #{$detail->id}");

                if ($detail->departemen_akun_id === null) {
                    continue; // already unassigned
                }

                $oldDaId = $detail->departemen_akun_id;
                $oldDeptId = $detail->departemenAkun ? $detail->departemenAkun->departemen_id : null;

                if ($isClosed && $overrideClosed && $entry) {
                    $affectedYears[(int) date('Y', strtotime($entryDate))] = true;
                }

                $detail->departemen_akun_id = null;
                $detail->save();

                $log = DepartmentAssignmentLog::create([
                    'journal_entry_detail_id' => $detail->id,
                    'journal_entry_id'        => $entry ? $entry->id : null,
                    'voucher'                 => $entry ? $entry->source : null,
                    'kode_akun'               => $detail->kode_akun,
                    'old_departemen_id'       => $oldDeptId,
                    'new_departemen_id'       => null,
                    'old_departemen_akun_id'  => $oldDaId,
                    'new_departemen_akun_id'  => null,
                    'action_type'             => 'unassign',
                    'reason'                  => $reason,
                    'user_id'                 => $user ? $user->id : null,
                    'batch_id'                => $batchId,
                    'is_override_closed'      => $isClosed && $overrideClosed,
                    'ip_address'              => request() ? request()->ip() : null,
                ]);

                $unassignedIds[] = $detail->id;
                $logIds[] = $log->id;
            }

            if (!empty($affectedYears)) {
                foreach (array_keys($affectedYears) as $year) {
                    $this->triggerRecalculateClosingIfNecessary($year);
                }
            }

            return [
                'success'          => true,
                'action'           => 'unassign_bulk',
                'unassigned_count' => count($unassignedIds),
                'batch_id'         => $batchId,
                'log_ids'          => $logIds,
                'message'          => "Sebanyak " . count($unassignedIds) . " transaksi berhasil dilepaskan dari departemen.",
            ];
        });
    }

    /**
     * Validate closed period guardrail and permissions.
     *
     * @param string $date
     * @param User|null $user
     * @param bool $overrideClosed
     * @param string $context
     * @return bool Whether the period is closed
     *
     * @throws AuthorizationException
     */
    protected function validateClosedPeriod(string $date, ?User $user, bool $overrideClosed, string $context = ''): bool
    {
        $isClosed = $this->isPeriodClosed($date);
        if ($isClosed) {
            $prefix = $context ? "{$context}: " : "";
            if (!$overrideClosed) {
                throw new AuthorizationException("{$prefix}Transaksi berada pada periode akuntansi tertutup ({$date}). Diperlukan hak akses khusus (override_closed) untuk melakukan penyesuaian.");
            }

            if ($user && method_exists($user, 'can') && !$user->can('assigned_watchdog.override_closed')) {
                throw new AuthorizationException("{$prefix}Pengguna tidak memiliki izin 'assigned_watchdog.override_closed' untuk mengubah transaksi pada periode tertutup.");
            }
        }
        return $isClosed;
    }

    /**
     * Recalculate closing retained earnings entries if closed period was modified.
     *
     * @param int $year
     * @return void
     */
    protected function triggerRecalculateClosingIfNecessary(int $year): void
    {
        $isClosed = DB::table('start_new_years')
            ->where('tahun', $year)
            ->where('status', 'Closing')
            ->exists();

        if ($isClosed) {
            try {
                if (class_exists(\App\Services\StartNewYearService::class)) {
                    $service = app(\App\Services\StartNewYearService::class);
                    $service->updateLabaTahunBerjalan($year);
                }
            } catch (\Exception $e) {
                Log::warning("[DepartmentAssignmentService] Gagal updateLabaTahunBerjalan({$year}): " . $e->getMessage());
            }
        }
    }
}
