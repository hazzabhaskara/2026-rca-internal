<?php

namespace App\Observers;

use App\TaxpayersProfile;

class TaxpayersProfileObserver
{
    /**
     * Handle the taxpayers profile "created" event.
     *
     * @param  \App\TaxpayersProfile  $taxpayersProfile
     * @return void
     */
    public function created(TaxpayersProfile $taxpayersProfile)
    {
        //
        logActivity("Menambahkan Taxpayer Profile dengan nama: {$taxpayersProfile->nama_perusahaan}");
    }

    /**
     * Handle the taxpayers profile "updated" event.
     *
     * @param  \App\TaxpayersProfile  $taxpayersProfile
     * @return void
     */
    public function updated(TaxpayersProfile $taxpayersProfile)
    {
        //
        logActivity("Mengubah Taxpayer Profile dengan nama: {$taxpayersProfile->nama_perusahaan}");
    }

    /**
     * Handle the taxpayers profile "deleted" event.
     *
     * @param  \App\TaxpayersProfile  $taxpayersProfile
     * @return void
     */
    public function deleted(TaxpayersProfile $taxpayersProfile)
    {
        //
        logActivity("Menghapus Taxpayer Profile dengan nama: {$taxpayersProfile->nama_perusahaan}");
    }

    /**
     * Handle the taxpayers profile "restored" event.
     *
     * @param  \App\TaxpayersProfile  $taxpayersProfile
     * @return void
     */
    public function restored(TaxpayersProfile $taxpayersProfile)
    {
        //
    }

    /**
     * Handle the taxpayers profile "force deleted" event.
     *
     * @param  \App\TaxpayersProfile  $taxpayersProfile
     * @return void
     */
    public function forceDeleted(TaxpayersProfile $taxpayersProfile)
    {
        //
    }
}
