<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('quantity', 15, 2);
            $table->decimal('order', 15, 2);
            $table->decimal('back_order', 15, 2)->default(0); // Jika barang belum tersedia
            $table->string('unit');                          // Satuan
            $table->string('item_description');
            $table->decimal('price', 15, 2);
            $table->decimal('tax', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->foreignId('account_id')->constrained('chart_of_accounts'); // akun pendapatan
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
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
        Schema::dropIfExists('purhase_invoice_details');
    }
}
