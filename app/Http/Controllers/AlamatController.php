<?php

namespace App\Http\Controllers;

use App\Kecamatan;
use App\Kelurahan;
use App\KotaIndonesia;
use App\ProvinceIndonesia;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    //

    public function searchProvinsi(Request $request)
    {
        $term = $request->q;
        $provinsi = ProvinceIndonesia::where('name', 'like', "%$term%")
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($provinsi);
    }
    public function searchKota(Request $request)
    {
        $term = $request->q;
        $kota = KotaIndonesia::where('name', 'like', "%$term%")
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($kota);
    }
    public function searchKecamatan(Request $request)
    {
        $term = $request->q;
        $kota = Kecamatan::where('name', 'like', "%$term%")
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($kota);
    }
    public function searchKelurahan(Request $request)
    {
        $term = $request->q;
        $kelurahan = Kelurahan::where('name', 'like', "%$term%")
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($kelurahan);
    }
}
