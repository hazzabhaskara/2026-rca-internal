<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitKerjaIdDiJenisHari extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('jenis_haris', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')->after('id')->nullable()->constrained('unit_kerjas');
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
        Schema::table('jenis_haris', function (Blueprint $table) {
            $table->dropForeign('unit_kerja_id');
            $table->dropColumn('unit_kerja_id');
        });
    }
}
