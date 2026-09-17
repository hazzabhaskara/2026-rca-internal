<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildOfBomDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('build_of_bom_details', function (Blueprint $table) {
            $table->id();

            // FK ke header build_of_boms
            $table->unsignedBigInteger('build_of_bom_id');
            $table->foreign('build_of_bom_id')
                ->references('id')
                ->on('build_of_boms')
                ->onDelete('cascade');

            // FK ke items (komponen yang dipakai)
            $table->unsignedBigInteger('component_item_id');
            $table->foreign('component_item_id')
                ->references('id')
                ->on('items');

            $table->string('unit');
            $table->decimal('qty_per_unit', 20, 4);
            $table->decimal('qty_total', 20, 4);
            $table->decimal('cost_component', 20, 2)->default(0);

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
        Schema::dropIfExists('build_of_bom_details');
    }
}
