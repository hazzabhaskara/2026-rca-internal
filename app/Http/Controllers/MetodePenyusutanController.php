<?php

namespace App\Http\Controllers;

use App\MetodePenyusutan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MetodePenyusutanController extends Controller
{
    //
    public function index()
    {
        $data = MetodePenyusutan::latest()->paginate(10);
        return view('metode_penyusutan.index', compact('data'));
    }
    public function create()
    {
        return view('metode_penyusutan.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'nama_metode' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255'
        ]);


        MetodePenyusutan::create($validated);

        return redirect()->route('metode_penyusutan.index')->with('success', 'Metode Penyusutan created successfully.');
    }
    public function show($id)
    {
        $data = MetodePenyusutan::findOrFail($id);

        return view('metode_penyusutan.show', compact('data'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $data = MetodePenyusutan::findOrFail($id);

        return view('metode_penyusutan.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_metode' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $data = MetodePenyusutan::findOrFail($id);

        $data->update($validated);

        return redirect()->route('metode_penyusutan.index')->with('success', 'Data berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $data = MetodePenyusutan::findOrFail($id);

        $data->delete();

        return redirect()->route('metode_penyusutan.index')->with('success', ' berhasil dihapus.');
    }
}
