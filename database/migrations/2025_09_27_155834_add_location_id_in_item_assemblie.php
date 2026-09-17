<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationIdInItemAssemblie extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_assemblies', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('from_location_id');
            $table->foreign('from_location_id')
                ->references('id')->on('location_inventories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_assemblie', function (Blueprint $table) {
            //
        });
    }
}
