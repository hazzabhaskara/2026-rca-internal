<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntangibleAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intangible_assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_asset')->unique();
            $table->string('nama_asset');
            $table->foreignId('kategori_id')->constrained('kategori_assets');
            $table->string('brand')->nullable();
            $table->text('deskripsi')->nullable();
            $table->foreignId('lokasi_id')->constrained('lokasis');
            $table->foreignId('golongan_id')->constrained('masa_manfaats');
            $table->string('dalam_tahun');
            $table->foreignId('metode_penyusutan_id')->constrained('metode_penyusutans');
            $table->decimal('tarif_amortisasi', 5, 2);
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
        Schema::dropIfExists('intangible_assets');
    }
}
