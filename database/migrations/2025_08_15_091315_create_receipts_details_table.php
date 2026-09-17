<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts_details', function (Blueprint $table) {
            $table->id();
            // Relasi ke header receipt
            $table->foreignId('receipt_id')->constrained('receipts')->onDelete('cascade');

            // Relasi ke sales_invoice
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->onDelete('restrict');

            $table->date('invoice_date');
            $table->decimal('original_amount', 15, 2);
            $table->decimal('amount_owing', 15, 2);
            $table->decimal('discount_available', 15, 2)->default(0);
            $table->decimal('discount_taken', 15, 2)->default(0);
            $table->decimal('amount_received', 15, 2);
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
        Schema::dropIfExists('receipts_details');
    }
}
