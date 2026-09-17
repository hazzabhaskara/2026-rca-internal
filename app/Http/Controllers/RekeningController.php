<?php

namespace App\Http\Controllers;

use App\Rekening;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    //
    public function index()
    {
        $data = Rekening::orderBy('nama_bank', 'asc')->paginate(10);
        return view('rekening.index', compact('data'));
    }
    public function create()
    {
        return view('rekening.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'atas_nama' => 'nullable|string|max:255',
            'nama_bank' => 'nullable|string|max:255',
            'no_rek' => 'nullable|string|max:255'
        ]);

        Rekening::create($validated);

        return redirect()->route('rekening.index')->with('success', 'Rekening created successfully.');
    }
    public function edit($id)
    {
        $data = Rekening::findOrFail($id);
        return view('rekening.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'atas_nama' => 'nullable|string|max:255',
            'nama_bank' => 'nullable|string|max:255',
            'no_rek' => 'nullable|string|max:255'
        ]);
        $rekening = Rekening::findOrFail($id);

        $rekening->update($validated);

        return redirect()->route('rekening.index')->with('success', 'Rekening created successfully.');
    }
    public function destroy($id)
    {
        $rekening = Rekening::findOrFail($id);

        $rekening->delete();

        return redirect()->route('rekening.index')->with('success', ' berhasil dihapus.');
    }
    public function search(Request $request)
    {
        $term = $request->q;

        $rekening = Rekening::where(function ($query) use ($term) {
            $query->where('atas_nama', 'like', "%$term%")
                ->orWhere('nama_bank', 'like', "%$term%")
                ->orWhere('no_rek', 'like', "%$term%");
        })
            ->select('id', 'atas_nama', 'nama_bank', 'no_rek')
            ->limit(20)
            ->get();

        return response()->json($rekening);
    }
}
