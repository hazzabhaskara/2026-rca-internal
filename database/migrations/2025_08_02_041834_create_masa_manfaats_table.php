<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasaManfaatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masa_manfaats', function (Blueprint $table) {
            $table->id();
            $table->string('jenis')->nullable();
            $table->string('masa_tahun');
            $table->string('kelompok_harta');
            $table->integer('golongan')->nullable();
            $table->string('nama_golongan');
            $table->decimal('tarif_penyusutan', 15, 2);
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
        Schema::dropIfExists('masa_manfaats');
    }
}
