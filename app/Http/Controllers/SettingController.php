<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    //
    public function index()
    {
        $tampilan = Setting::all()->pluck('value', 'key');
        return view('setting.index', compact('tampilan'));
    }
    public function update(Request $request)
    {
        // Validasi
        $request->validate([
            'theme_color'   => 'nullable|string',
            'text_footer'   => 'nullable|string',
            'theme_secondary_color'   => 'nullable|string',
            'site_title'    => 'nullable|string',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'background'    => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        // Set teks dan warna
        Setting::set('theme_color', $request->theme_color);
        Setting::set('theme_secondary_color', $request->theme_secondary_color);
        Setting::set('text_footer', $request->text_footer);
        Setting::set('site_title', $request->site_title);

        // Upload logo jika ada
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('uploads/Setting', 'public');

            // Hapus file lama jika ada
            $old = Setting::get('logo');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            Setting::set('logo', $path);
        }

        // Upload background jika ada
        if ($request->hasFile('background')) {
            $file = $request->file('background');
            $path = $file->store('uploads/Setting', 'public');

            $old = Setting::get('background');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            Setting::set('background', $path);
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
