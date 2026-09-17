<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemAssemblieDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_assemblie_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_assembly_id');
            $table->foreign('item_assembly_id')
                ->references('id')->on('item_assemblies')
                ->onDelete('cascade');

            // Komponen item
            $table->unsignedBigInteger('component_item_id');
            $table->foreign('component_item_id')
                ->references('id')->on('items')
                ->onDelete('cascade');

            $table->string('unit');
            $table->decimal('qty_used', 20, 2);   // berapa qty komponen dipakai
            $table->decimal('unit_cost', 20, 2);  // ambil dari avg cost item_quantities
            $table->decimal('total_cost', 20, 2); // qty_used × unit_cost
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
        Schema::dropIfExists('item_assemblie_details');
    }
}
