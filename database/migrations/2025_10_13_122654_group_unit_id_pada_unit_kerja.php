<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GroupUnitIdPadaUnitKerja extends Migration
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
            $table->foreignId('group_unit_id')->after('id')->nullable()->constrained('group_units');
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
        Schema::table('unit_Kerjas', function (Blueprint $table) {
            $table->dropForeign('group_unit_id');
            $table->dropColumn('group_unit_id');
        });
    }
}
