<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepartemenIdDiWahanaItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('wahana_items', function (Blueprint $table) {
            $table->unsignedBigInteger('departemen_id')->nullable()->after('account_id');

            $table->foreign('departemen_id')->references('id')->on('departements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        schema::table('wahana_items', function (Blueprint $table) {
            // Hapus foreign key constraint
            $table->dropForeign(['departemen_id']);

            // Hapus kolomnya
            $table->dropColumn(['departemen_id']);
        });
    }
}
