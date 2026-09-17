<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJamSelesaiJamMulaiInJenisHari extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jenis_haris', function (Blueprint $table) {
            //
            $table->time('jam_mulai')->after('deskripsi');
            $table->time('jam_selesai')->after('jam_mulai');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jenis_hari', function (Blueprint $table) {
            //
        });
    }
}
