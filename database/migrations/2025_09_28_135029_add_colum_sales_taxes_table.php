<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumSalesTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('sales_taxes', function (Blueprint $table) {
            // tambah kolom rate dengan tipe decimal, max 999.99
            $table->decimal('rate', 5, 2)->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('sales_taxes', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
    }
}
