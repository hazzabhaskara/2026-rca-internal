<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeFormatClosingPadaUnitKerja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('unit_kerjas', function (Blueprint $table) {
            $table->string('kode_unit')->after('group_unit_id');
            $table->integer('format_closing')->after('urutan');
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
        Schema::table('unit_kerjas', function (Blueprint $table) {
            $table->dropColumn('kode_unit');
            $table->dropColumn('format_closing');
        });
    }
}
