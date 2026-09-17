<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInvoiceDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoice_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->onDelete('cascade');
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
        Schema::dropIfExists('sales_invoice_documents');
    }
}
