<?php

namespace App\Http\Controllers;

use App\ApprovalStep;
use App\Jabatan;
use Illuminate\Http\Request;

class ApprovalStepController extends Controller
{
    //
    public function index()
    {
        $data = ApprovalStep::select('approval_steps.*')
            ->orderBy('step_order', 'asc')
            ->get();
        return view('approval_step.index', compact('data'));
    }
    public function create()
    {
        $jabatan = Jabatan::all();
        return view('approval_step.create', compact('jabatan'));
    }
    public function store(Request $request)
    {

        // dd($request->all());
        // ✅ Validasi input
        $request->validate([
            'jabatan_id'   => 'required|array',
            'jabatan_id.*' => 'required|exists:jabatans,id',
            'step_order'   => 'required|array',
            'step_order.*' => 'required|numeric|min:1',
        ]);

        $data = [];

        foreach ($request->jabatan_id as $index => $jabatanId) {
            // Lewati jika kosong (meskipun sudah divalidasi)
            if (empty($jabatanId)) continue;

            $data[] = [
                'jabatan_id' => $jabatanId,
                'step_order' => $request->step_order[$index] ?? ($index + 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($data)) {
            ApprovalStep::insert($data);
            return redirect()
                ->route('approval_step.index')
                ->with('success', 'Data approval step berhasil ditambahkan.');
        }

        return back()->with('error', 'Tidak ada data yang valid untuk disimpan.');
    }
    public function edit($id)
    {
        $approval_step = ApprovalStep::with(['jabatan'])->findOrFail($id);
        $jabatan = Jabatan::all();
        return view('approval_step.edit', compact('approval_step', 'jabatan'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'jabatan_id' => 'required|exists:jabatans,id',
            'step_order' => 'required|numeric|min:1',
        ]);

        $approval_step = ApprovalStep::findOrFail($id);

        $approval_step->update($validated);

        return redirect()->route('approval_step.index')->with('success', 'Approval step berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $approval_step = ApprovalStep::findOrFail($id);

        $approval_step->delete();

        return redirect()->route('approval_step.index')->with('success', ' Data berhasil dihapus.');
    }
}
