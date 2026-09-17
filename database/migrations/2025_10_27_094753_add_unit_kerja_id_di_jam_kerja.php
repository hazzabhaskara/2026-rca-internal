<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitKerjaIdDiJamKerja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('jamkerjas', function (Blueprint $table) {
            // Tambahkan kolom payment_id setelah kolom id (atau di posisi yang kamu inginkan)
            $table->foreignId('unit_kerja_id')
                ->nullable()
                ->after('id')
                ->constrained('unit_kerjas')
                ->onDelete('cascade');
            // "cascade" agar jika payment dihapus, alokasi ikut terhapus
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
        Schema::table('unit_kerjas', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropColumn('unit_kerja_id');
        });
    }
}
