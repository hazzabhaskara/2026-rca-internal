<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisHariIdInTransaksiWahanas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaksi_wahanas', function (Blueprint $table) {
            //
            $table->foreignId('jenis_hari_id')->constrained('jenis_haris')->cascadeOnDelete()->after('wahana_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaksi_wahanas', function (Blueprint $table) {
            //
        });
    }
}
