<?php

namespace App\Http\Controllers;

use App\SettingDepartement;
use Illuminate\Http\Request;

class SettingDepartementController extends Controller
{
    //

    // Tampilkan form pengaturan
    public function edit()
    {
        // Cek apakah sudah ada setting, jika belum buat default
        $setting = SettingDepartement::firstOrCreate(
            ['key' => 'current_department'],
            ['value' => '-'] // default non aktif
        );

        return view('admin.setting_departement', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'value' => 'required|string'
        ]);

        SettingDepartement::updateOrCreate(
            ['key' => 'current_department'],
            ['value' => $request->value]
        );

        return redirect()->route('setting_departement.edit')->with('success', 'Mode departemen berhasil diperbarui.');
    }
}
