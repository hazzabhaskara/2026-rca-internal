<?php

namespace App\Http\Controllers;

use App\Ptkp;
use App\TaxRates;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxRatesController extends Controller
{
    //
    public function index()
    {
        $data = TaxRates::with('ptkp')->orderBy('ptkp_id')->get();
        return view('tax_rates.index', compact('data'));
    }
    public function create()
    {
        $ptkp = DB::table('ptkps')->select('id', 'kategori')->distinct()->get();
        return view('tax_rates.create', compact('ptkp'));
    }
    public function store(Request $request)
    {
        // Bersihkan titik ribuan sebelum validasi
        $request->merge([
            'min_penghasilan' => str_replace('.', '', $request->min_penghasilan),
            'max_penghasilan' => str_replace('.', '', $request->max_penghasilan),
        ]);

        // Validasi setelah input dibersihkan
        $validated = $request->validate([
            'ptkp_id' => 'required|exists:ptkps,id',
            'min_penghasilan' => 'required|numeric',
            'max_penghasilan' => 'required|numeric|gt:min_penghasilan',
            'tarif_ter' => 'required|numeric',
        ]);

        TaxRates::create($validated);

        return redirect()->route('tax_rates.index')->with('success', 'Data TER berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $tax_rates = TaxRates::findOrFail($id);

        $tax_rates->delete();

        return redirect()->route('tax_rates.index')->with('success', ' Data berhasil dihapus.');
    }
    public function show($id)
    {
        $tax_rates = TaxRates::findOrFail($id);

        return view('tax_rates.show', compact('tax_rates'));
    }
    public function edit($id)
    {
        $tarif_ter = \App\TaxRates::findOrFail($id);
        $ptkp = Ptkp::all();

        return view('tax_rates.edit', compact('tarif_ter', 'ptkp'));
    }
    public function update(Request $request, TaxRates $taxRate)
    {
        $request->merge([
            'min_penghasilan' => str_replace('.', '', $request->min_penghasilan),
            'max_penghasilan' => str_replace('.', '', $request->max_penghasilan),
        ]);

        $validated = $request->validate([
            'ptkp_id' => 'required|exists:ptkps,id',
            'min_penghasilan' => 'required|numeric',
            'max_penghasilan' => 'required|numeric|gt:min_penghasilan',
            'tarif_ter' => 'required|numeric',
        ]);

        $taxRate->update($validated);

        return redirect()->route('tax_rates.index')->with('success', 'Data TER berhasil diperbarui');
    }
}
