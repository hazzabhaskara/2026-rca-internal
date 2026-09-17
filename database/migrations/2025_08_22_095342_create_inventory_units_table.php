<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->boolean('selling_same_as_stocking')->default(true);
            $table->string('selling_unit')->nullable();
            $table->integer('selling_relationship')->nullable(); // berapa per unit stocking

            $table->boolean('buying_same_as_stocking')->default(true);
            $table->string('buying_unit')->nullable();
            $table->integer('buying_relationship')->nullable();
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
        Schema::dropIfExists('inventory_units');
    }
}
