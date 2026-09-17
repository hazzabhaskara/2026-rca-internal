<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTangibleAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tangible_assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_asset')->unique();
            $table->string('nama_asset');
            $table->foreignId('kategori_id')->constrained('kategori_assets');
            $table->string('merk')->nullable();
            $table->string('tanggal_perolehan')->nullable();
            $table->string('nilai_perolehan')->nullable();
            $table->foreignId('lokasi_id')->constrained('lokasis');
            $table->string('components')->nullable();
            $table->string('capacity')->nullable();
            $table->string('type')->nullable();
            $table->foreignId('golongan_id')->constrained('masa_manfaats');
            $table->foreignId('metode_penyusutan_id')->constrained('metode_penyusutans');
            $table->string('dalam_tahun');
            $table->decimal('tarif_penyusutan', 5, 2);
            $table->string('asset_full_name');
            $table->string('foto')->nullable();
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
        Schema::dropIfExists('tangible_assets');
    }
}
