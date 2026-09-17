<?php

namespace App\Http\Controllers;

use App\Ptkp;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PtkpController extends Controller
{
    //
    public function index()
    {
        $ptkp = Ptkp::latest()->paginate(10);
        return view('ptkp.index', compact('ptkp'));
    }
    public function create()
    {
        return view('ptkp.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'kategori' => 'nullable|string',
            'nilai' => 'nullable|integer'

        ]);

        Ptkp::create($validated);

        return redirect()->route('ptkp.index')->with('success', 'PTKP berhasil ditambahkan.');
    }
    public function show($id)
    {
        $ptkp = Ptkp::findOrFail($id);

        return view('ptkp.show', compact('ptkp'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $ptkp = Ptkp::findOrFail($id);

        return view('ptkp.edit', compact('ptkp'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'kategori' => 'nullable|string',
            'nilai' => 'nullable|integer'
        ]);

        $ptkp = Ptkp::findOrFail($id);

        $ptkp->update($validated);

        return redirect()->route('ptkp.index')->with('success', 'ptkp berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $ptkp = Ptkp::findOrFail($id);

        $ptkp->delete();

        return redirect()->route('ptkp.index')->with('success', ' berhasil dihapus.');
    }
}
