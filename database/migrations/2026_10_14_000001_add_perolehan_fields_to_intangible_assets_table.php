<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerolehanFieldsToIntangibleAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            if (!Schema::hasColumn('intangible_assets', 'tanggal_perolehan')) {
                $table->date('tanggal_perolehan')->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('intangible_assets', 'nilai_perolehan')) {
                $table->decimal('nilai_perolehan', 15, 2)->default(0)->after('tanggal_perolehan');
            }
            if (!Schema::hasColumn('intangible_assets', 'asset_full_name')) {
                $table->string('asset_full_name')->nullable()->after('tarif_amortisasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intangible_assets', function (Blueprint $table) {
            $cols = [];
            foreach (['tanggal_perolehan', 'nilai_perolehan', 'asset_full_name'] as $col) {
                if (Schema::hasColumn('intangible_assets', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
}
