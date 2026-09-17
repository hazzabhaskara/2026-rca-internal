<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranGajisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembayaran_gajis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kode_karyawan');
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->date('tanggal_pembayaran');
            $table->timestamps();

            $table->foreign('kode_karyawan')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_gajis');
    }
}
