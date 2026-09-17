<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengajuansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengajuan')->unique();
            $table->date('tgl_pengajuan');
            $table->text('keterangan')->nullable();
            $table->foreignId('no_rek_id')->constrained('rekenings')->cascadeOnDelete(); // pengaju
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete(); // pengaju
            $table->timestamp('tgl_proses')->nullable(); // terakhir disetujui
            $table->enum('status', ['draft', 'in_progress', 'approved', 'rejected', 'cancelled'])->default('draft');
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
        Schema::dropIfExists('pengajuans');
    }
}
