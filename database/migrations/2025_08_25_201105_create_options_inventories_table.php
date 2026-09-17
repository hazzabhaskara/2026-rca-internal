<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options_inventories', function (Blueprint $table) {
            $table->id();
            $table->enum('costing_method', ['average', 'fifo']);
            $table->enum('profit_eval_method', ['markup', 'margin']);
            $table->enum('sort_inventory_service', ['number', 'description']);
            $table->boolean('allow_below_zero')->default(0);
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
        Schema::dropIfExists('options_inventories');
    }
}
