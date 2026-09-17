<?php

namespace App\Http\Controllers;

use App\Taxes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaxesController extends Controller
{
    //
    public function index()
    {
        $data = Taxes::orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
        return view('taxes.index', compact('data'));
    }
    public function create()
    {
        return view('taxes.create');
    }
    public function store(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'bulan'         => 'required|integer|min:1|max:12',
            'tahun'         => 'required|integer|min:2000|max:2100',
            'jenis_pajak'   => 'required|string|max:255',
            'jenis_dokumen' => 'required|string|max:255',
            'file_path'     => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        // 2. Upload file
        $filePath = null;
        if ($request->hasFile('file_path')) {
            // Simpan ke storage/app/public/taxes
            $filePath = $request->file('file_path')->store('taxes', 'public');
        }

        // 3. Simpan ke database
        Taxes::create([
            'bulan'         => $validated['bulan'],
            'tahun'         => $validated['tahun'],
            'jenis_pajak'   => $validated['jenis_pajak'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'file_path'     => $filePath,
        ]);

        // 4. Redirect kembali dengan pesan sukses
        return redirect()->route('taxes.index')
            ->with('success', 'Data pajak berhasil disimpan.');
    }
    public function destroy(Taxes $tax)
    {
        // Hapus file fisik kalau ada
        if ($tax->file_path && Storage::disk('public')->exists($tax->file_path)) {
            Storage::disk('public')->delete($tax->file_path);
        }

        // Hapus data dari database
        $tax->delete();

        return redirect()->route('taxes.index')->with('success', 'Data pajak & file berhasil dihapus.');
    }
    public function edit(Taxes $tax)
    {
        return view('taxes.edit', compact('tax'));
    }
    public function update(Request $request, Taxes $tax)
    {
        $validated = $request->validate([
            'bulan'         => 'required|integer|min:1|max:12',
            'tahun'         => 'required|integer|min:2000|max:2100',
            'jenis_pajak'   => 'required|string|max:255',
            'jenis_dokumen' => 'required|string|max:255',
            'file_path'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $filePath = $tax->file_path;

        // Kalau ada file baru diupload
        if ($request->hasFile('file_path')) {
            // Hapus file lama
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Simpan file baru
            $filePath = $request->file('file_path')->store('taxes', 'public');
        }

        // Update database
        $tax->update([
            'bulan'         => $validated['bulan'],
            'tahun'         => $validated['tahun'],
            'jenis_pajak'   => $validated['jenis_pajak'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'file_path'     => $filePath,
        ]);

        return redirect()->route('taxes.index')->with('success', 'Data pajak berhasil diperbarui.');
    }
    public function show(Taxes $tax)
    {
        return view('taxes.show', compact('tax'));
    }
}
