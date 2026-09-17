<?php

namespace App\Http\Controllers;

use App\salesOption;
use Illuminate\Http\Request;

class salesOptionsController extends Controller
{
    //
    public function index()
    {
        $options = salesOption::first();

        // Jika belum ada data, buat default
        if (!$options) {
            $options = salesOption::create([
                'aging_first_period' => 30,
                'aging_second_period' => 60,
                'aging_third_period' => 90,
                'discount_type' => 'before_tax',
            ]);
        }

        return view('sales_option.index', compact('options'));
    }
    public function create()
    {
        return view('sales_option.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'aging_first_period' => 'required|integer|min:0',
            'aging_second_period' => 'required|integer|min:0',
            'aging_third_period' => 'required|integer|min:0',
            'discount_type' => 'required|in:before_tax,after_tax',
        ]);

        // Cegah duplikat (karena hanya boleh satu baris)
        if (salesOption::count() > 0) {
            return redirect()->route('sales_option.index')->with('warning', 'Sales Option sudah pernah disimpan.');
        }

        salesOption::create($validated);

        return redirect()->route('sales_option.index')->with('success', 'Pengaturan berhasil dibuat.');
    }

    public function edit()
    {
        $options = salesOption::first();
        return view('sales_option.edit', compact('options'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'aging_first_period' => 'required|integer|min:0',
            'aging_second_period' => 'required|integer|min:0',
            'aging_third_period' => 'required|integer|min:0',
            'discount_type' => 'required|in:before_tax,after_tax',
        ]);

        $options = salesOption::first();
        $options->update($validated);

        return redirect()->route('sales.options.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
