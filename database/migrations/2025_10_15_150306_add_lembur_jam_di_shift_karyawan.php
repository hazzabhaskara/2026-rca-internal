<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLemburJamDiShiftKaryawan extends Migration
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
            $table->decimal('lembur_jam', 5, 2)->nullable()->after('persentase_jam');
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
            $table->dropColumn('lembur_jam');
        });
    }
}
