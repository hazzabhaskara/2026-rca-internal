<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomposisiGajiDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('komposisi_gaji_details', function (Blueprint $table) {
            // Ganti nama kolom agar konsisten: 'kd_komposisi' -> 'komposisi_gaji_id'
            $table->unsignedBigInteger('komposisi_gaji_id');
            $table->unsignedBigInteger('kode_komponen');

            $table->double('nilai')->default(0);
            $table->integer('jumlah_hari')->nullable();

            $table->double('potongan')->default(0);
            $table->integer('urut')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('komposisi_gaji_id')->references('id')->on('komposisi_gajis')->onDelete('cascade');
            $table->foreign('kode_komponen')->references('id')->on('komponen_penghasilans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('komposisi_gaji_details');
    }
}
