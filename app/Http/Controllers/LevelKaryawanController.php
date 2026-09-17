<?php

namespace App\Http\Controllers;

use App\LevelKaryawan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LevelKaryawanController extends Controller
{
    //
    public function index()
    {
        $levelKaryawan = LevelKaryawan::all();
        return view('LevelKaryawan.index', compact('levelKaryawan'));
    }
    public function create()
    {
        return view('LevelKaryawan.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_level' => 'required|string',
            'deskripsi' => 'nullable|string',

        ]);

        LevelKaryawan::create($validated);

        return redirect()->route('LevelKaryawan.index')->with('success', 'Level Karyawan berhasil ditambahkan.');
    }
    public function show($id)
    {
        $levelKaryawan = LevelKaryawan::findOrFail($id);

        return view('LevelKaryawan.show', compact('levelKaryawan'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $levelKaryawan = LevelKaryawan::findOrFail($id);

        return view('LevelKaryawan.edit', compact('levelKaryawan'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_level' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $levelKaryawan = LevelKaryawan::findOrFail($id);

        $levelKaryawan->update($validated);

        return redirect()->route('LevelKaryawan.index')->with('success', 'Level Karyawan berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $levelKaryawan = LevelKaryawan::findOrFail($id);

        $levelKaryawan->delete();

        return redirect()->route('LevelKaryawan.index')->with('success', ' berhasil dihapus.');
    }
}
