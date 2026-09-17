<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoiceDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->onDelete('cascade');
            $table->string('document_name'); // Nama dokumen (misal: PO, Invoice, dll)
            $table->string('file_path');     // Lokasi file di storage
            $table->string('file_type')->nullable(); // pdf, docx, xlsx, jpg, dll
            $table->integer('file_size')->nullable(); // ukuran file (byte)
            $table->text('description')->nullable();  // keterangan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice_documents');
    }
}
