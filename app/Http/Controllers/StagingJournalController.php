<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StagingJournalEntry;
use App\ChartOfAccount;
use App\Departement;
use App\Services\JournalIngestionService;

class StagingJournalController extends Controller
{
    public function index(Request $request)
    {
        $status  = $request->get('status', 'failed');
        $entries = StagingJournalEntry::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $coas        = ChartOfAccount::select('kode_akun', 'nama_akun')->orderBy('kode_akun')->get();
        $departements = Departement::select('id', 'kode', 'deskripsi')->orderBy('kode')->get();

        return view('staging_journal.index', compact('entries', 'status', 'coas', 'departements'));
    }

    /**
     * Update kode_akun dan/atau departemen pada baris staging, kembalikan ke 'pending'.
     */
    public function update(Request $request, $id)
    {
        $entry = StagingJournalEntry::findOrFail($id);

        $updateData = ['status' => 'pending', 'error_message' => null];

        if ($request->filled('kode_akun')) {
            $updateData['kode_akun'] = $request->kode_akun;
        }
        if ($request->has('departemen')) {
            $updateData['departemen'] = $request->departemen ?: null;
        }

        $entry->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate, status kembali ke pending untuk diproses ulang.',
        ]);
    }

    /**
     * Proses ulang semua data pending (All-or-Nothing).
     */
    public function reprocess(JournalIngestionService $service)
    {
        $result = $service->processPendingEntries();

        if ($result['status'] === 'empty') {
            return redirect()->back()->with('info', $result['message']);
        }

        if ($result['status'] === 'validation_error') {
            return redirect()
                ->to(route('staging_journal.index', ['status' => 'failed']))
                ->with('validation_errors', $result['errors'])
                ->with('error_summary', $result['summary'])
                ->with('error_message_text', $result['message']);
        }

        if ($result['status'] === 'success') {
            return redirect()
                ->to(route('staging_journal.index', ['status' => 'success']))
                ->with('success', $result['message'] . ' Berhasil: ' . $result['processed_rows'] . ' baris.');
        }

        // internal error
        return redirect()->back()->with('danger', $result['message']);
    }
}
