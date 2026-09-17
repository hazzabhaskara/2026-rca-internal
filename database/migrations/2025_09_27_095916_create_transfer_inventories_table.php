<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_location_id');
            $table->foreign('from_location_id')
                ->references('id')->on('location_inventories')
                ->onDelete('cascade');
            $table->unsignedBigInteger('to_location_id');
            $table->foreign('to_location_id')
                ->references('id')->on('location_inventories')
                ->onDelete('cascade');
            $table->string('source');
            $table->date('date');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('transfer_inventories');
    }
}
