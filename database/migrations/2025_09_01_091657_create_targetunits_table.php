<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetunitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('targetunits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_kerja_id')
                ->constrained('unit_kerjas')
                ->cascadeOnDelete();

            // Relasi ke komponen_penghasilans
            $table->foreignId('komponen_penghasilan_id')
                ->constrained('komponen_penghasilans')
                ->cascadeOnDelete();

            // Target bulanan & besaran
            $table->decimal('target_bulanan', 15, 2)->nullable();

            $table->foreignId('level_karyawan_id')
                ->nullable()
                ->constrained('level_karyawans')
                ->nullOnDelete();

            $table->decimal('besaran_nominal', 15, 2)->nullable();

            // Periode
            $table->integer('bulan');
            $table->integer('tahun');
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
        Schema::dropIfExists('targetunits');
    }
}
