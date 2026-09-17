<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesTaxIdDiPaymentExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_expense_details', function (Blueprint $table) {
            $table->dropColumn('tax');
            $table->foreignId('sales_taxes_id')->nullable()->constrained('sales_taxes');
        });
    }

    public function down()
    {
        Schema::table('payment_expense_details', function (Blueprint $table) {
            // solusi 1 (singkat)
            $table->dropForeign(['sales_taxes_id']); // hapus constraint FK
            $table->dropColumn('sales_taxes_id');    // baru drop kolom

            // solusi 2 (manual)
            // $table->dropForeign(['sales_taxes_id']);
            // $table->dropColumn('sales_taxes_id');

            $table->decimal('tax', 15, 2);
        });
    }
}
