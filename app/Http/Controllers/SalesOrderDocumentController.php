<?php

namespace App\Http\Controllers;

use App\SalesOrderDocument;
use App\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalesOrderDocumentController extends Controller
{
    public function index()
    {
        $documents = SalesOrderDocument::with('salesOrder')->latest()->paginate(20);
        return view('sales_order.documents.index', compact('documents'));
    }

    public function store(Request $request, $salesOrderId)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('sales_order_documents', 'public'); // simpan ke storage/app/public/sales_order_documents

        SalesOrderDocument::create([
            'sales_order_id' => $salesOrderId,
            'document_name' => $request->document_name,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Dokumen berhasil diupload');
    }



    public function destroy($id)
    {
        $document = SalesOrderDocument::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus');
    }
}
