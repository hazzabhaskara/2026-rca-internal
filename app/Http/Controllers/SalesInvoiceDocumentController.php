<?php

namespace App\Http\Controllers;

use App\SalesInvoiceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalesInvoiceDocumentController extends Controller
{
    public function index()
    {
        $documents = SalesInvoiceDocument::with('salesInvoice')->latest()->paginate(20);
        return view('sales_invoice.documents.index', compact('documents'));
    }
    public function store(Request $request, $salesInvoiceId)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('sales_invoice_document', 'public'); // simpan ke storage/app/public/sales_order_documents

        SalesInvoiceDocument::create([
            'sales_invoice_id' => $salesInvoiceId,
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
        $document = SalesInvoiceDocument::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus');
    }
}
