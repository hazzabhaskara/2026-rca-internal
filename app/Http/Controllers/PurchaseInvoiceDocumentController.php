<?php

namespace App\Http\Controllers;

use App\PurchaseInvoiceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PurchaseInvoiceDocumentController extends Controller
{
    public function index()
    {
        $documents = PurchaseInvoiceDocument::with('purchaseInvoice')->latest()->paginate(20);
        return view('purchase_invoice.documents.index', compact('documents'));
    }

    public function store(Request $request, $purchaseInvoiceId)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('purchase_invoice_documents', 'public'); // simpan ke storage/app/public/sales_order_documents

        PurchaseInvoiceDocument::create([
            'purchase_invoice_id' => $purchaseInvoiceId,
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
        $document = PurchaseInvoiceDocument::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus');
    }
}
