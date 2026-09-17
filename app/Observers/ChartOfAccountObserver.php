<?php

namespace App\Observers;

use App\ChartOfAccount;

class ChartOfAccountObserver
{
    /**
     * Handle the chart of account "created" event.
     *
     * @param  \App\ChartOfAccount  $chartOfAccount
     * @return void
     */
    public function created(ChartOfAccount $chartOfAccount)
    {
        //
        logActivity("Menambahkan ChartOfAccount dengan kode: {$chartOfAccount->kode_akun}");
    }

    /**
     * Handle the chart of account "updated" event.
     *
     * @param  \App\ChartOfAccount  $chartOfAccount
     * @return void
     */
    public function updated(ChartOfAccount $chartOfAccount)
    {
        //
        logActivity("Mengubah ChartOfAccount dengan kode: {$chartOfAccount->kode_akun}");
    }

    /**
     * Handle the chart of account "deleted" event.
     *
     * @param  \App\ChartOfAccount  $chartOfAccount
     * @return void
     */
    public function deleted(ChartOfAccount $chartOfAccount)
    {
        //
        logActivity("Menghapus ChartOfAccount dengan kode: {$chartOfAccount->kode_akun}");
    }

    /**
     * Handle the chart of account "restored" event.
     *
     * @param  \App\ChartOfAccount  $chartOfAccount
     * @return void
     */
    public function restored(ChartOfAccount $chartOfAccount)
    {
        //
    }

    /**
     * Handle the chart of account "force deleted" event.
     *
     * @param  \App\ChartOfAccount  $chartOfAccount
     * @return void
     */
    public function forceDeleted(ChartOfAccount $chartOfAccount)
    {
        //
    }
}
