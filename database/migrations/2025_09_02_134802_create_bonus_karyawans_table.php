<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusKaryawansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shift_karyawan_wahanas')->cascadeOnDelete();
            $table->foreignId('transaksi_wahana_id')->nullable()->constrained('transaksi_wahanas')->nullOnDelete();

            // Periode
            $table->date('tanggal');
            $table->foreignId('jenis_hari_id')->constrained('jenis_haris');

            // Nilai perhitungan
            $table->decimal('bonus', 15, 2)->default(0);         // bonus per shift
            $table->decimal('transportasi', 15, 2)->default(0);  // misal Rp 20.000 fix
            $table->decimal('total', 15, 2)->default(0);         // bonus + transport

            // Status
            $table->enum('status', ['Pending', 'Calculated', 'Finalized'])
                ->default('Pending');

            // Audit
            $table->text('keterangan')->nullable();

            // Unik supaya tidak dobel
            $table->unique(['employee_id', 'shift_id'], 'uniq_bonus_per_shift');
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
        Schema::dropIfExists('bonus_karyawans');
    }
}
