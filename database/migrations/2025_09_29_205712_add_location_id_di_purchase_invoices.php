<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationIdDiPurchaseInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('purchase_invoices', function (Blueprint $table) {


            // tambahkan kolom tax_id sebagai relasi ke sales_taxes
            $table->foreignId('location_id')
                ->nullable()
                ->constrained('location_inventories')
                ->nullOnDelete();
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
        Schema::table('purchase_invoices', function (Blueprint $table) {


            // tambahkan kolom tax_id sebagai relasi ke sales_taxes
            $table->dropColumn('location_id');
        });
    }
}
