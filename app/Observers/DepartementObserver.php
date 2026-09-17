<?php

namespace App\Observers;

use App\Departement;

class DepartementObserver
{
    /**
     * Handle event setelah departement dibuat
     */
    public function created(Departement $departement)
    {
        logActivity("Menambahkan Departemen dengan kode: {$departement->kode}");
    }

    /**
     * Handle event setelah departement diupdate
     */
    public function updated(Departement $departement)
    {
        logActivity("Mengubah Departemen dengan kode: {$departement->kode}");
    }

    /**
     * Handle event setelah departement dihapus
     */
    public function deleted(Departement $departement)
    {
        logActivity("Menghapus Departemen dengan kode: {$departement->kode}");
    }
}
