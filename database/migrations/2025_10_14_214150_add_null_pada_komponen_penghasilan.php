<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullPadaKomponenPenghasilan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('komponen_penghasilans', function (Blueprint $table) {
            $table->dropColumn('kategori');
            $table->string('deskripsi')->nullable()->after('tipe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('komponen_penghasilans', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
            $table->string('kategori');
        });
    }
}
