<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_quantities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->string('location')->default('Primary location');
            $table->integer('on_hand_qty')->default(0);
            $table->decimal('on_hand_value', 15, 2)->default(0);
            $table->integer('pending_orders_qty')->default(0);
            $table->decimal('pending_orders_value', 15, 2)->default(0);
            $table->integer('purchase_order_qty')->default(0);
            $table->integer('sales_order_qty')->default(0);
            $table->integer('reorder_minimum')->nullable();
            $table->integer('reorder_to_order')->nullable();
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
        Schema::dropIfExists('inventory_quantities');
    }
}
