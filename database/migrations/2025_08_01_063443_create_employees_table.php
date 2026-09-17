<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('kode_karyawan');
            $table->string('nama_karyawan');
            $table->string('nik');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('golongan_darah')->nullable();
            $table->string('tinggi_badan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('agama')->nullable();
            $table->string('kewarganegaraan')->nullable();
            $table->string('status_pernikahan')->nullable();
            $table->string('ptkp_id')->constrained('ptkps')->onDelete('cascade');
            $table->string('jabatan_id')->constrained('jabatans')->onDelete('cascade');
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->string('status_pegawai')->nullable();
            $table->string('level_kepegawaian_id')->constrained('level_karyawans')->onDelete('cascade');
            $table->string('unit_kerja_id')->constrained('unit_kerjas')->onDelete('cascade');
            $table->string('sertifikat')->nullable();
            $table->string('photo')->nullable();
            $table->string('foto_ktp')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
