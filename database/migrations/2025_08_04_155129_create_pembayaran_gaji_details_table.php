<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranGajiDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembayaran_gaji_details', function (Blueprint $table) {
            $table->id();
            // Ganti nama kolom agar konsisten: 'kd_komposisi' -> 'komposisi_gaji_id'
            $table->unsignedBigInteger('kode_pembayaran_id');
            $table->unsignedBigInteger('kode_komponen');

            $table->double('nilai')->default(0);
            $table->double('potongan')->default(0);
            $table->integer('urut')->nullable();
            $table->string('jumlah_hari');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_gaji_details');
    }
}
