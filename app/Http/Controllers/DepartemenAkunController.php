<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use Illuminate\Http\Request;

class DepartemenAkunController extends Controller
{
    //
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        $query = chartOfAccount::with('departemenAkun.departemen')
            ->whereNotIn('level_akun', ['HEADER', 'GROUP ACCOUNT', 'X', 'Level Akun']);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('kode_akun', 'like', "%{$q}%")
                    ->orWhere('nama_akun', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('kode_akun')->limit(50)->get();

        return response()->json($items->map(function ($item) {
            return [
                'id' => $item->id,
                'kode_akun' => $item->kode_akun,
                'nama_akun' => $item->nama_akun,
                'tipe_akun' => $item->tipe_akun,
                'departemen_id' => optional($item->departemenAkun->first())->departemen_id,
                'nama_departemen' => optional(optional($item->departemenAkun->first())->departemen)->deskripsi ?? '-',
                'daftar_departemen' => $item->departemenAkun->map(function ($d) {
                    return [
                        'id' => $d->id, // <-- ini id pivot dari tabel departemen_akuns
                        'deskripsi' => optional($d->departemen)->deskripsi ?? '-'
                    ];
                })
            ];
        }));
    }
}
