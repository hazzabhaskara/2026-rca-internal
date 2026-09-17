<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahKolomJumlahPengunjungPadaClosingHarian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('closing_harians', function (Blueprint $table) {
            $table->integer('jumlah_pengunjung')->after('total_lebih_kurang');
        });
        Schema::table('wahana_items', function (Blueprint $table) {
            $table->integer('dasar_perhitungan_titipan')->after('departemen_id');
            $table->decimal('harga_perhitungan_titipan', 15, 2)->after('dasar_perhitungan_titipan');
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
        Schema::table('closing_harians', function (Blueprint $table) {
            $table->dropColumn('jumlah_pengunjung');
        });
        Schema::table('wahana_items', function (Blueprint $table) {
            $table->dropColumn('dasar_perhitungan_titipan');
            $table->dropColumn('harga_perhitungan_titipan');
        });
    }
}
