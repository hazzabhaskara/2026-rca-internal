<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesDepositDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_deposit_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deposit_id');
            $table->date('invoice_date')->nullable();
            $table->unsignedBigInteger('sales_invoice_id')->nullable();
            $table->decimal('original_amount', 15, 2)->nullable();
            $table->decimal('amount_owing', 15, 2)->nullable();
            $table->decimal('discount_available', 15, 2)->nullable();
            $table->decimal('discount_taken', 15, 2)->nullable();
            $table->timestamps();

            $table->foreign('deposit_id')->references('id')->on('sales_deposits')->onDelete('cascade');
            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_deposit_details');
    }
}
