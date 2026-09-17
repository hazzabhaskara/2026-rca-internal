<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    //
    public function index()
    {
        $data = project::latest()->paginate(10);
        return view('project.index', compact('data'));
    }
    public function create()
    {
        return view('project.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_project' => 'required|string|max:255',
            'start_date' => 'date',
            'end_date' => 'nullable|date',
            'revenue' => 'nullable',
            'expens' => 'nullable',
            'status' => 'required|string'
        ]);

        // Bersihkan format ribuan (hilangkan titik)
        $validated['revenue'] = str_replace('.', '', $validated['revenue']);
        $validated['expens']  = str_replace('.', '', $validated['expens']);

        // Simpan ke database
        Project::create($validated);

        return redirect()->route('project.index')->with('success', 'Data created successfully.');
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);

        return view('project.show', compact('project'));
    }
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        return view('project.edit', compact('project'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_project' => 'required|string|max:255',
            'start_date' => 'date',
            'end_date' => 'nullable|date',
            'revenue' => 'nullable',
            'expens' => 'nullable',
            'status' => 'required|string'
        ]);

        // Bersihkan format ribuan
        $validated['revenue'] = str_replace('.', '', $validated['revenue']);
        $validated['expens']  = str_replace('.', '', $validated['expens']);

        $project = Project::findOrFail($id);

        // Gunakan data yang sudah dibersihkan
        $project->update($validated);

        return redirect()->route('project.index')->with('success', 'Data berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $data = Project::findOrFail($id);

        $data->delete();

        return redirect()->route('project.index')->with('success', ' Data Berhasil Dihapus.');
    }

    public function projectView(Request $request)
    {
        $query = Project::query();

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('start_date', [$request->tanggal_awal, $request->tanggal_akhir]);
        }
        $entries = $query->get();

        if ($entries->isEmpty()) {
            return redirect()->route('project.view_project')
                ->withInput()
                ->with('not_found', 'Data tidak ditemukan untuk filter yang dipilih.');
        }

        return view('project.view_project', compact('entries'));
    }

    public function projectEdit(Request $request)
    {
        $query = Project::query();

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('start_date', [$request->tanggal_awal, $request->tanggal_akhir]);
        }
        $entries = $query->get();

        if ($entries->isEmpty()) {
            return redirect()->route('project.edit_project')
                ->withInput()
                ->with('not_found', 'Data tidak ditemukan untuk filter yang dipilih.');
        }

        return view('project.edit_project', compact('entries'));
    }
}
