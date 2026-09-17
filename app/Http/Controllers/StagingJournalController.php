<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StagingJournalEntry;
use App\ChartOfAccount;
use App\Services\JournalIngestionService;

class StagingJournalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'failed');
        $entries = StagingJournalEntry::where('status', $status)->orderBy('created_at', 'desc')->paginate(50);
        $coas = ChartOfAccount::select('kode_akun', 'nama_akun')->get();

        return view('staging_journal.index', compact('entries', 'status', 'coas'));
    }

    public function update(Request $request, $id)
    {
        $entry = StagingJournalEntry::findOrFail($id);
        
        $entry->update([
            'kode_akun' => $request->kode_akun,
            'status' => 'pending' // Set to pending to be reprocessed
        ]);

        return response()->json(['success' => true, 'message' => 'Akun berhasil diupdate, status kembali ke pending.']);
    }

    public function reprocess(JournalIngestionService $service)
    {
        // Re-process all pending entries
        $result = $service->processPendingEntries();
        
        return redirect()->back()->with('success', $result['message'] . ' Berhasil: ' . $result['processed_rows'] . ' Baris, Gagal: ' . $result['failed_rows'] . ' Baris.');
    }
}
