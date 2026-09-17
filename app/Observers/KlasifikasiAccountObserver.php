<?php

namespace App\Observers;

use App\KlasifikasiAkun;

class KlasifikasiAccountObserver
{
    /**
     * Handle the klasifikasi akun "created" event.
     *
     * @param  \App\KlasifikasiAkun  $klasifikasiAkun
     * @return void
     */
    public function created(KlasifikasiAkun $klasifikasiAkun)
    {
        //
        logActivity("Menambahkan Klasifikasi Account dengan kode: {$klasifikasiAkun->kode_klasifikasi}");
    }

    /**
     * Handle the klasifikasi akun "updated" event.
     *
     * @param  \App\KlasifikasiAkun  $klasifikasiAkun
     * @return void
     */
    public function updated(KlasifikasiAkun $klasifikasiAkun)
    {
        //
        logActivity("Mengubah Klasifikasi Account dengan kode: {$klasifikasiAkun->kode_klasifikasi}");
    }

    /**
     * Handle the klasifikasi akun "deleted" event.
     *
     * @param  \App\KlasifikasiAkun  $klasifikasiAkun
     * @return void
     */
    public function deleted(KlasifikasiAkun $klasifikasiAkun)
    {
        //
        logActivity("Menghapus Klasifikasi Account dengan kode: {$klasifikasiAkun->kode_klasifikasi}");
    }

    /**
     * Handle the klasifikasi akun "restored" event.
     *
     * @param  \App\KlasifikasiAkun  $klasifikasiAkun
     * @return void
     */
    public function restored(KlasifikasiAkun $klasifikasiAkun)
    {
        //
    }

    /**
     * Handle the klasifikasi akun "force deleted" event.
     *
     * @param  \App\KlasifikasiAkun  $klasifikasiAkun
     * @return void
     */
    public function forceDeleted(KlasifikasiAkun $klasifikasiAkun)
    {
        //
    }
}
