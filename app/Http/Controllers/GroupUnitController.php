<?php

namespace App\Http\Controllers;

use App\GroupUnit;
use Illuminate\Http\Request;

class GroupUnitController extends Controller
{
    //
    //
    public function index()
    {
        $data = GroupUnit::orderBy('nama', 'asc')->get();
        return view('group_unit.index', compact('data'));
    }
    public function create()
    {
        return view('group_unit.create');
    }
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|array',
            'deskripsi' => 'nullable|array',
        ]);
        $data = [];

        for ($i = 0; $i < count($request->nama); $i++) {
            // Lewati jika nama kosong
            if (empty($request->nama[$i])) continue;

            $data[] = [
                'nama' => $request->nama[$i],
                'deskripsi' => $request->deskripsi[$i] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (count($data)) {
            GroupUnit::insert($data);
            return redirect()->route('group_unit.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return back()->with('error', 'Tidak ada data yang dimasukkan.');
        }
    }
    public function edit($id)
    {
        $data = GroupUnit::findOrFail($id);
        return view('group_unit.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',

        ]);

        $group_unit = GroupUnit::findOrFail($id);

        $group_unit->update($validated);

        return redirect()->route('group_unit.index')->with('success', 'Crew berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $group_unit = GroupUnit::findOrFail($id);

        $group_unit->delete();

        return redirect()->route('group_unit.index')->with('success', ' Data berhasil dihapus.');
    }
}
