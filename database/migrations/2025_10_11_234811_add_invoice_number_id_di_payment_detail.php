<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceNumberIdDiPaymentDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('payment_details', function (Blueprint $table) {
            $table->foreignId('invoice_number_id')->nullable()->constrained('purchase_invoices');
            $table->dropColumn('invoice_number');
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
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropForeign('invoice_number_id');
            $table->dropColumn('invoice_number_id');
            $table->string('invoice_number');
        });
    }
}
