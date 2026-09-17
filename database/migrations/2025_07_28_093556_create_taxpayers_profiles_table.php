<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxpayersProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxpayers_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->string('jalan');
            $table->string('kelurahan');
            $table->string('kecamatan');
            $table->string('kota');
            $table->string('provinsi');
            $table->string('kode_pos');
            $table->string('logo')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('bentuk_badan_hukum');
            $table->string('npwp');
            $table->string('klu_code')->nullable();
            $table->text('klu_description')->nullable();
            $table->string('tax_office')->nullable();
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
        Schema::dropIfExists('taxpayers_profiles');
    }
}
