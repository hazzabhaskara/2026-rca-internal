<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;

use App\DepartemenAkun;
use App\Departement;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    //
    public function index()
    {
        $departemens = Departement::all();
        return view('departemen.index', compact('departemens'));
    }
    public function create()
    {
        return view('departemen.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:departements,kode',
            'deskripsi' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        Departement::create($validated);



        return redirect()->route('departemen.index')->with('success', 'Departemen berhasil ditambahkan.');
    }
    public function destroy($id)
    {
        $departemen = Departement::findOrFail($id);
        $departemen->delete();

        return redirect()->route('departemen.index')->with('success', 'Departemen berhasil dihapus.');
    }


    public function edit($id)
    {
        $departemen = Departement::findOrFail($id);
        return view('departemen.edit', compact('departemen'));
    }

    public function update(Request $request, $id)
    {
        $departemen = Departement::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|unique:departements,kode,' . $departemen->id,
            'deskripsi' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $departemen->update($validated);
        return redirect()->route('departemen.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    public function show($id)
    {
        $departemen = Departement::findOrFail($id);
        return view('departemen.show', compact('departemen'));
    }
    public function assign(Request $request)
    {
        $departemenList = Departement::all();
        $departemenId = $request->get('departemen_id');

        // Akun yang sudah di-assign ke departemen terpilih
        $departemenAccounts = [];
        $nonDepartemenAccounts = [];

        if ($departemenId) {
            // Ambil hanya akun yang sudah diassign ke departemen tersebut (level Account/Sub Account, case-insensitive)
            $departemenAccounts = DepartemenAkun::with(['chartOfAccount' => function ($q) {
                $q->whereRaw('LOWER(level_akun) IN (?, ?)', ['account', 'sub account']);
            }, 'departemen'])
                ->where('departemen_id', $departemenId)
                ->get();

            // Ambil akun yang BELUM didepartemenkan ke departemen tersebut
            $assignedIdsInThisDepartemen = DepartemenAkun::where('departemen_id', $departemenId)
                ->pluck('akun_id');

            $nonDepartemenAccounts = ChartOfAccount::whereNotIn('id', $assignedIdsInThisDepartemen)
                ->whereRaw('LOWER(level_akun) IN (?, ?)', ['account', 'sub account']) // tambahkan ini juga
                ->get();
        }

        return view('departemen.assign', compact(
            'departemenList',
            'departemenAccounts',
            'nonDepartemenAccounts',
            'departemenId'
        ));
    }


    public function storeAssign(Request $request)
    {
        $request->validate([
            'departemen_id' => 'required|exists:departements,id',
            'akun_ids'      => 'required|array',
            'akun_ids.*'    => 'exists:chart_of_accounts,id',
        ]);

        foreach ($request->akun_ids as $akun_id) {
            // Cek jika sudah pernah diassign ke departemen yang sama
            $exists = DepartemenAkun::where('departemen_id', $request->departemen_id)
                ->where('akun_id', $akun_id)
                ->exists();

            if (!$exists) {
                DepartemenAkun::create([
                    'departemen_id' => $request->departemen_id,
                    'akun_id'       => $akun_id,
                ]);
            }
        }

        return redirect()->route('departemen.assign')->with('success', 'Akun berhasil di-assign ke departemen.');
    }
    public function destroyAssign($id)
    {
        $assign = DepartemenAkun::findOrFail($id);
        $assign->delete();

        return redirect()->back()->with('success', 'Akun berhasil dihapus dari departemen.');
    }

    public function destroyAssignBulk(Request $request)
    {
        $request->validate([
            'departemen_id' => 'required|exists:departements,id',
            'remove_akun_ids' => 'required|array',
            'remove_akun_ids.*' => 'exists:departemen_akuns,id',
        ]);

        DepartemenAkun::whereIn('id', $request->remove_akun_ids)
            ->where('departemen_id', $request->departemen_id)
            ->delete();

        return redirect()->back()->with('success', 'Akun berhasil dihapus dari departemen.');
    }
}
