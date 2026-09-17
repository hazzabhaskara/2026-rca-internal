<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahJumlahPengunjungMinMaxPadaJenisHari extends Migration
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
            $table->integer('jumlah_pengunjung_min')->after('jam_selesai');
            $table->integer('jumlah_pengunjung_max')->after('jumlah_pengunjung_min');
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
            $table->dropColumn('jumlah_pengunjung_min');
            $table->dropColumn('jumlah_pengunjung_max');
        });
    }
}
