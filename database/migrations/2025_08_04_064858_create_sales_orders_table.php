<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->date('date_order');
            $table->date('shipping_date')->nullable();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('jenis_pembayaran_id')->constrained('payment_methods');
            $table->text('shipping_address')->nullable();
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
        Schema::dropIfExists('sales_orders');
    }
}
