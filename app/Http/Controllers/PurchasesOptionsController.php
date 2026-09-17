<?php

namespace App\Http\Controllers;

use App\PurchaseOptions;
use Illuminate\Http\Request;

class PurchasesOptionsController extends Controller
{
    //
    public function index()
    {
        $data = PurchaseOptions::first();
        if (!$data) {
            $data = PurchaseOptions::create([
                'aging_first_period' => 30,
                'aging_second_period' => 60,
                'aging_third_period' => 90,
            ]);
        }
        return view('purchases_options.index', compact('data'));
    }
    public function create()
    {
        return view('purchases_options.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'aging_first_period' => 'required|integer|min:0',
            'aging_second_period' => 'required|integer|min:0',
            'aging_third_period' => 'required|integer|min:0',
        ]);

        // Cegah duplikat (karena hanya boleh satu baris)
        if (PurchaseOptions::count() > 0) {
            return redirect()->route('purchases_options.index')->with('warning', 'Purchases Option sudah pernah disimpan.');
        }

        PurchaseOptions::create($request->all());

        return redirect()->route('purchases_options.index')->with('success', 'Pengaturan berhasil dibuat.');
    }
}
