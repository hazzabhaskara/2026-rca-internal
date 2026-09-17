<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('customers_id')->constrained('customers');
            $table->foreignId('jenis_pembayaran_id')->constrained('payment_methods');
            $table->text('shipping_address')->nullable();
            $table->date('shipping_date')->nullable();
            $table->foreignId('sales_person_id')->nullable()->constrained('employees');
            $table->decimal('freight', 15, 2)->default(0);
            $table->string('early_payment_terms')->nullable();
            $table->text('messages')->nullable();
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
        Schema::dropIfExists('sales_invoices');
    }
}
