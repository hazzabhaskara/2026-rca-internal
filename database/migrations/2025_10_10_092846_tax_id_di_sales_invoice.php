<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TaxIdDiSalesInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sales_invoice_details', function (Blueprint $table) {


            // tambahkan kolom tax_id sebagai relasi ke sales_taxes
            $table->foreignId('tax_id')
                ->nullable()
                ->constrained('sales_taxes')
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
        Schema::table('sales_invoice_details', function (Blueprint $table) {
            $table->dropForeign('tax_id');
            $table->dropColumn('tax_id');
        });
    }
}
