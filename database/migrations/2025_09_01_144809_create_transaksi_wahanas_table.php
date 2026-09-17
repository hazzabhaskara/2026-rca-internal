<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksiWahanasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi_wahanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_kerja_id')
                ->constrained('unit_kerjas')
                ->cascadeOnDelete();
            $table->foreignId('wahana_id')
                ->constrained('wahanas')
                ->cascadeOnDelete();

            $table->date('tanggal');
            $table->decimal('realisasi', 15, 2);
            $table->integer('jumlah_pengunjung')->nullable();

            $table->timestamps();

            // Unik per (unit_kerja, wahana, tanggal)
            $table->unique(['unit_kerja_id', 'wahana_id', 'tanggal'], 'uniq_transaksi_wahana');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi_wahanas');
    }
}
