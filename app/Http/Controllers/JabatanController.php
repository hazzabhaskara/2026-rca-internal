<?php

namespace App\Http\Controllers;

use App\Jabatan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{
    //
    public function index(): View
    {
        $jabatans = Jabatan::orderBy('kd_jabatan', 'asc')->paginate(10);
        return view('jabatan.index', compact('jabatans'));
    }

    public function search(Request $request)
    {
        $term = $request->q;
        $jabatan = Jabatan::where('nama_jabatan', 'like', "%$term%")
            ->select('id', 'nama_jabatan')
            ->limit(20)
            ->get();

        return response()->json($jabatan);
    }

    public function create(): View
    {
        return view('jabatan.create');
    }
    private function generateKodeJabatan()
    {
        $last = \App\Jabatan::orderBy('kd_jabatan', 'desc')->first();

        if ($last && preg_match('/JAB-(\d+)/', $last->kd_jabatan, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return 'JAB-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'kd_jabatan' => 'nullable|string|max:255',
            'nama_jabatan' => 'nullable|string|max:255',
            'desc_jabatan' => 'nullable|string|max:255'
        ]);

        // Kalau kosong (auto generate), buat kode otomatis
        if (empty($validated['kd_jabatan'])) {
            $validated['kd_jabatan'] = $this->generateKodeJabatan();
        }

        Jabatan::create($validated);

        return redirect()->route('jabatan.index')->with('success', 'Jabatan created successfully.');
    }
    public function show($kd_jabatan)
    {
        $jabatans = Jabatan::where('kd_jabatan', $kd_jabatan)->firstOrFail();
        return view('jabatan.show', compact('jabatans'));
    }
    public function edit($kd_jabatan)
    {
        $jabatan = Jabatan::where('kd_jabatan', $kd_jabatan)->firstOrFail();
        return view('jabatan.edit', compact('jabatan'));
    }
    public function update(Request $request, $kd_jabatan)
    {
        $jabatan = Jabatan::where('kd_jabatan', $kd_jabatan)->firstOrFail();
        $validated = $request->validate([
            'kd_jabatan' => 'nullable|string|max:255',
            'nama_jabatan' => 'nullable|string|max:255',
            'desc_jabatan' => 'nullable|string|max:255'
        ]);

        $jabatan->update($validated);

        return redirect()->route('jabatan.index')->with('success', 'Jabatan updated successfully.');
    }
    public function destroy($id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $jabatan = Jabatan::with(['employee'])->findOrFail($id);

                // 🚫 Cek apakah sudah dipakai di Invoice
                if ($jabatan->employee()->exists()) {
                    throw new \Exception("Jabatan ini sudah digunakan dalam Employee tidak bisa dihapus.");
                }

                // ✅ Kalau aman, hapus (details ikut terhapus otomatis via cascade)
                $jabatan->delete();
            });

            return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('jabatan.index')->with('error', $e->getMessage());
        }
    }
}
