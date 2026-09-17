<?php

namespace App\Http\Controllers;

use App\BonusKaryawan;
use App\JenisHari;
use App\Wahana;
use Illuminate\Http\Request;

class BonusKaryawanController extends Controller
{
    //
    public function index()
    {
        $query = BonusKaryawan::with(['employee', 'shift', 'transaksiWahana.wahana', 'jenisHari']);

        // Filter Level Karyawan
        if ($wahana = request('filter_wahana')) {
            $query->whereHas('transaksiWahana.wahana', function ($q) use ($wahana) {
                $q->where('nama_wahana', $wahana);
            });
        }
        if ($jenisHari = request('filter_jenis_hari')) {
            $query->whereHas('jenisHari', function ($q) use ($jenisHari) {
                $q->where('nama', $jenisHari);
            });
        }
        $searchable = ['tanggal', 'keterangan',];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }


                // tambahkan juga relasi
                $q->orWhereHas('jenisHari', function ($q3) use ($search) {
                    $q3->where('nama', 'like', "%{$search}%");
                });
                $q->orWhereHas('transaksiWahana.wahana', function ($q4) use ($search) {
                    $q4->where('nama_wahana', 'like', "%{$search}%");
                });
                $q->orWhereHas('employee', function ($q5) use ($search) {
                    $q5->where('nama_karyawan', 'like', "%{$search}%");
                });
            });
        }


        // Eksekusi query sekali di akhir
        $data = $query->get();
        // Atau kalau mau paginasi:
        // $data = $query->paginate(20)->appends(request()->query());

        // Sumber data untuk dropdown
        $jenis_hari = JenisHari::select('nama')->distinct()->orderBy('nama')->pluck('nama');
        $wahana = Wahana::select('nama_wahana')->distinct()->orderBy('nama_wahana')->pluck('nama_wahana');
        return view('bonus_karyawan.index', compact('data', 'jenis_hari', 'wahana'));
    }
}
