<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusSalesInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sales_invoices', function (Blueprint $table) {
            //
            $table->integer('status_sales_invoice')->nullable()->after('messages');
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
        Schema::table('sales_invoices', function (Blueprint $table) {
            //
            $table->dropColumn('status_sales_invoice');
        });
    }
}
