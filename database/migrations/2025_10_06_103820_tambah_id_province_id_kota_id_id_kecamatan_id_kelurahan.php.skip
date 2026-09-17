<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahIdProvinceIdKotaIdIdKecamatanIdKelurahan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->char('id_provinsi', 2)->nullable();
            $table->foreign('id_provinsi')
                ->references('id')->on('indonesia_provinces')
                ->nullOnDelete();

            $table->char('id_kota', 4)->nullable();
            $table->foreign('id_kota')
                ->references('id')->on('indonesia_cities')
                ->nullOnDelete();

            $table->char('id_kecamatan', 8)->nullable();
            $table->foreign('id_kecamatan')
                ->references('id')->on('indonesia_districts')
                ->nullOnDelete();

            $table->char('id_kelurahan', 12)->nullable();
            $table->foreign('id_kelurahan')
                ->references('id')->on('indonesia_villages')
                ->nullOnDelete();

            $table->dropColumn(['kelurahan', 'kecamatan', 'kota', 'provinsi']);
        });

        Schema::table('taxpayers_profiles', function (Blueprint $table) {
            $table->char('id_provinsi', 2)->nullable();
            $table->foreign('id_provinsi')
                ->references('id')->on('indonesia_provinces')
                ->nullOnDelete();

            $table->char('id_kota', 4)->nullable();
            $table->foreign('id_kota')
                ->references('id')->on('indonesia_cities')
                ->nullOnDelete();

            $table->char('id_kecamatan', 8)->nullable();
            $table->foreign('id_kecamatan')
                ->references('id')->on('indonesia_districts')
                ->nullOnDelete();

            $table->char('id_kelurahan', 12)->nullable();
            $table->foreign('id_kelurahan')
                ->references('id')->on('indonesia_villages')
                ->nullOnDelete();

            $table->dropColumn(['kelurahan', 'kecamatan', 'kota', 'provinsi']);
        });
    }

    public function down()
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropForeign(['id_provinsi']);
            $table->dropForeign(['id_kota']);
            $table->dropForeign(['id_kecamatan']);
            $table->dropForeign(['id_kelurahan']);

            $table->dropColumn(['id_provinsi', 'id_kota', 'id_kecamatan', 'id_kelurahan']);

            $table->string('kelurahan');
            $table->string('kecamatan');
            $table->string('kota');
            $table->string('provinsi');
        });

        Schema::table('taxpayers_profiles', function (Blueprint $table) {
            $table->dropForeign(['id_provinsi']);
            $table->dropForeign(['id_kota']);
            $table->dropForeign(['id_kecamatan']);
            $table->dropForeign(['id_kelurahan']);

            $table->dropColumn(['id_provinsi', 'id_kota', 'id_kecamatan', 'id_kelurahan']);

            $table->string('kelurahan');
            $table->string('kecamatan');
            $table->string('kota');
            $table->string('provinsi');
        });
    }
}
