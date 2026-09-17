<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_quantities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('location_inventories');
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
        Schema::dropIfExists('item_quantities');
    }
}
