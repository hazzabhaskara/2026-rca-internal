<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UnitKerjaIdDanDepartemenIdPadaLinkedAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('linked_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_kerja_id')->nullable()->after('akun_id');
            $table->unsignedBigInteger('departemen_id')->nullable()->after('unit_kerja_id');

            $table->foreign('unit_kerja_id')->references('id')->on('unit_kerjas');
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
        schema::table('linked_accounts', function (Blueprint $table) {
            // Hapus foreign key constraint
            $table->dropForeign(['unit_kerja_id']);
            $table->dropForeign(['departemen_id']);

            // Hapus kolomnya
            $table->dropColumn(['unit_kerja_id', 'departemen_id']);
        });
    }
}
