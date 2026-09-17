<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountDiPurchaseOrderDanInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)->after('price');
        });

        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)->after('price');
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
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('discount');
        });

        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
}
