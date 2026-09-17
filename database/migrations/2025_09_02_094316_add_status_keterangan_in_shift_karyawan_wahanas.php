<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusKeteranganInShiftKaryawanWahanas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shift_karyawan_wahanas', function (Blueprint $table) {
            //
            $table->enum('status', ['Penetapan', 'Perubahan', 'Tambahan'])
                ->default('Penetapan')
                ->after('persentase_jam');
            $table->text('keterangan')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shift_karyawan_wahanas', function (Blueprint $table) {
            //
        });
    }
}
