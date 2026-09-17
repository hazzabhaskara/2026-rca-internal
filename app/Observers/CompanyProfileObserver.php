<?php

namespace App\Observers;

use App\CompanyProfile;

class CompanyProfileObserver
{
    /**
     * Handle the company profile "created" event.
     *
     * @param  \App\CompanyProfile  $companyProfile
     * @return void
     */
    public function created(CompanyProfile $companyProfile)
    {
        //
        logActivity("Menambahkan Company Profile dengan nama: {$companyProfile->nama_perusahaan}");
    }

    /**
     * Handle the company profile "updated" event.
     *
     * @param  \App\CompanyProfile  $companyProfile
     * @return void
     */
    public function updated(CompanyProfile $companyProfile)
    {
        //
        logActivity("Mengubah Company Profile dengan nama: {$companyProfile->nama_perusahaan}");
    }

    /**
     * Handle the company profile "deleted" event.
     *
     * @param  \App\CompanyProfile  $companyProfile
     * @return void
     */
    public function deleted(CompanyProfile $companyProfile)
    {
        //
        logActivity("Menghapus Company Profile dengan nama: {$companyProfile->nama_perusahaan}");
    }

    /**
     * Handle the company profile "restored" event.
     *
     * @param  \App\CompanyProfile  $companyProfile
     * @return void
     */
    public function restored(CompanyProfile $companyProfile)
    {
        //
    }

    /**
     * Handle the company profile "force deleted" event.
     *
     * @param  \App\CompanyProfile  $companyProfile
     * @return void
     */
    public function forceDeleted(CompanyProfile $companyProfile)
    {
        //
    }
}
