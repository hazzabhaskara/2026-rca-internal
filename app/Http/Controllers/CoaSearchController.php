<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use Illuminate\Http\Request;

class CoaSearchController extends Controller
{
    //
    public function search(Request $request)
    {
        $q    = trim($request->get('q', ''));
        $page = (int) $request->get('page', 1);
        $perPage = 20;

        $query = chartOfAccount::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('kode_akun', 'like', "%{$q}%")
                    ->orWhere('nama_akun', 'like', "%{$q}%");
            });
        }

        // hindari N+1 sorting berat; kombinasi kode lalu nama
        $query->orderBy('kode_akun')->orderBy('nama_akun');

        $total = $query->count();

        $items = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'kode_akun', 'nama_akun']);

        // Format Select2
        $results = $items->map(function ($acc) {
            return [
                'id'   => $acc->id,
                'text' => ($acc->kode_akun ?? 'KODE') . ' - ' . ($acc->nama_akun ?? 'NAMA AKUN'),
                // ekstra buat templating
                'kode' => $acc->kode_akun,
                'nama' => $acc->nama_akun,
            ];
        });

        return response()->json([
            'results'    => $results,
            'pagination' => ['more' => ($page * $perPage) < $total],
        ]);
    }
}
