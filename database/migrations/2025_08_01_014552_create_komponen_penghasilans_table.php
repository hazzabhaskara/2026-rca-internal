<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomponenPenghasilansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('komponen_penghasilans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_komponen');
            $table->string('tipe');
            $table->string('kategori');
            $table->string('sifat');
            $table->string('periode_perhitungan');
            $table->string('status_komponen');
            $table->foreignId('level_karyawan_id')->constrained('level_karyawans');
            $table->boolean('cek_komponen')->default(false);
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
        Schema::dropIfExists('komponen_penghasilans');
    }
}
