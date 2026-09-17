<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetWahanasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target_wahanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wahana_id')->constrained('wahanas')->onDelete('cascade');
            $table->foreignId('jenis_hari_id')->constrained('jenis_haris')->onDelete('cascade');
            $table->integer('target_harian');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('target_wahanas');
    }
}
