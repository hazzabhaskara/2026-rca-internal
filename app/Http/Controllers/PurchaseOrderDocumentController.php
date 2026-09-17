<?php

namespace App\Http\Controllers;

use App\PurchaseOrderDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderDocumentController extends Controller
{
    public function index()
    {
        $documents = PurchaseOrderDocument::with('purchaseOrder')->latest()->paginate(20);
        return view('purchase_order.documents.index', compact('documents'));
    }

    public function store(Request $request, $purchaseOrderId)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('purchase_order_documents', 'public'); // simpan ke storage/app/public/sales_order_documents

        PurchaseOrderDocument::create([
            'purchase_order_id' => $purchaseOrderId,
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
        $document = PurchaseOrderDocument::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus');
    }
}
