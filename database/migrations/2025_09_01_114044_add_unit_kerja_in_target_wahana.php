<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitKerjaInTargetWahana extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('target_wahanas', function (Blueprint $table) {
            // Hapus kolom integer lama
            $table->dropColumn('target_harian');
        });

        Schema::table('target_wahanas', function (Blueprint $table) {
            // Tambah ulang dengan decimal
            $table->decimal('target_harian', 15, 2)->after('jenis_hari_id');
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->onDelete('cascade')->after('wahana_id');
        });
    }

    public function down(): void
    {
        Schema::table('target_wahanas', function (Blueprint $table) {
            $table->dropColumn('target_harian');
        });

        Schema::table('target_wahanas', function (Blueprint $table) {
            $table->integer('target_harian')->after('jenis_hari_id');
            $table->dropForeign(['unit_kerja_id']);
            $table->dropColumn('unit_kerja_id');
        });
    }
}
