<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferInventoryDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_inventory_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_inventory_id');
            $table->foreign('transfer_inventory_id')
                ->references('id')->on('transfer_inventories')
                ->onDelete('cascade');
            $table->unsignedBigInteger('component_item_id');
            $table->foreign('component_item_id')
                ->references('id')->on('items')
                ->onDelete('cascade');
            $table->string('unit');
            $table->integer('qty');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('amount', 15, 2);
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
        Schema::dropIfExists('transfer_inventory_details');
    }
}
