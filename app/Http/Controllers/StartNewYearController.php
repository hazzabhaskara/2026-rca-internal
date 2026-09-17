<?php

namespace App\Http\Controllers;

use App\StartNewYear;
use Facade\FlareClient\View;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Http\Request;

class StartNewYearController extends Controller
{
    //

    public function index()
    {
        $data = StartNewYear::all();
        return view('start_new_year.index', compact('data'));
    }
    public function create()
    {
        return view('start_new_year.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'awal_periode' => 'date',
            'akhir_periode' => 'date',
            'status' => 'required|string'
        ]);
        // Simpan ke database
        StartNewYear::create($validated);

        return redirect()->route('start_new_year.index')->with('success', 'Data created successfully.');
    }
    public function show($id)
    {
        $data = StartNewYear::findOrFail($id);

        return view('start_new_year.show', compact('data'));
    }
    public function edit(string $id): ViewView
    {
        //get post by ID
        $data = StartNewYear::findOrFail($id);

        return view('start_new_year.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'awal_periode' => 'date',
            'akhir_periode' => 'date',
            'status' => 'required|string'
        ]);

        $data = StartNewYear::findOrFail($id);

        $data->update($request->all());

        return redirect()->route('start_new_year.index')->with('success', 'Data berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $data = StartNewYear::findOrFail($id);

        $data->delete();

        return redirect()->route('start_new_year.index')->with('success', ' berhasil dihapus.');
    }
}
