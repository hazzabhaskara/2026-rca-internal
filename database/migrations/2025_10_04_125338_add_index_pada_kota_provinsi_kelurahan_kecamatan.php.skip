<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexPadaKotaProvinsiKelurahanKecamatan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indonesia_provinces', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('indonesia_cities', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('indonesia_districts', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('indonesia_villages', function (Blueprint $table) {
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::table('indonesia_provinces', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('indonesia_cities', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('indonesia_districts', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
        Schema::table('indonesia_villages', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
}
