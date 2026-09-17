<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftKaryawanWahanasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_karyawan_wahanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->cascadeOnDelete();
            $table->foreignId('wahana_id')->constrained('wahanas')->cascadeOnDelete();

            // Informasi hari & waktu
            $table->date('tanggal');
            $table->foreignId('jenis_hari_id')->constrained('jenis_haris'); // weekday/weekend/full day
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            // Proporsi
            $table->decimal('lama_jam', 5, 2)->nullable();        // hasil hitung jam_selesai - jam_mulai
            $table->decimal('persentase_jam', 5, 2)->nullable(); // lama_jam / jam_default_jenis_hari

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
        Schema::dropIfExists('shift_karyawan_wahanas');
    }
}
