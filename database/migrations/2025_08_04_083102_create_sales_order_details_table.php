<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->string('item_description');
            $table->decimal('quantity', 15, 2);
            $table->decimal('back_order', 15, 2)->default(0); // Jika barang belum tersedia
            $table->string('unit');                          // Satuan
            $table->decimal('base_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);   // Diskon per item
            $table->decimal('price', 15, 2);                 // Harga setelah diskon
            $table->decimal('amount', 15, 2);                // Subtotal
            $table->decimal('tax', 15, 2)->default(0);       // Pajak per item
            $table->foreignId('account_id')->constrained('chart_of_accounts'); // akun pendapatan
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
        Schema::dropIfExists('sales_order_details');
    }
}
