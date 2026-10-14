<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Setting;

class FiscalSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:fiscal.access');
    }

    public function index()
    {
        $fiscalJournalMode = Setting::get('fiscal_journal_mode', 'disabled');
        return view('fiscal_setting.index', compact('fiscalJournalMode'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'fiscal_journal_mode' => 'required|in:disabled,hide,enabled',
        ]);

        Setting::set('fiscal_journal_mode', $validated['fiscal_journal_mode']);

        return redirect()->route('fiscal_setting.index')
            ->with('success', 'Pengaturan format fiskal General Journal berhasil disimpan.');
    }
}
