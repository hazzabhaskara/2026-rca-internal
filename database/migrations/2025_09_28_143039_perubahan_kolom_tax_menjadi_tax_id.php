<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PerubahanKolomTaxMenjadiTaxId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            // hapus kolom tax lama
            $table->dropColumn('tax');

            // tambahkan kolom tax_id sebagai relasi ke sales_taxes
            $table->foreignId('tax_id')
                ->nullable()
                ->constrained('sales_taxes')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn('tax');

            // balikin kolom lama kalau rollback
            $table->decimal('tax', 15, 2)->nullable();
        });
    }
}
