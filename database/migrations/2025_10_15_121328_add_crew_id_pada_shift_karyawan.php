<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrewIdPadaShiftKaryawan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('shift_karyawan_wahanas', function (Blueprint $table) {
            $table->foreignId('crew_id')->nullable()->after('wahana_id')->constrained('crew_shift_karyawans');
            $table->dropColumn('posisi');
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
        Schema::table('shift_karyawan_wahanas', function (Blueprint $table) {
            $table->dropForeign('crew_id');
            $table->dropColumn('crew_id');
            $table->enum('posisi', ['petugas_1', 'petugas_2', 'petugas_3', 'petugas_4', 'pengganti'])->after('keterangan');
        });
    }
}
