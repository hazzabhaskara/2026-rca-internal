<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAccountIdToHeaderAccountIdOnPurchaseInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            }

            $table->foreignId('payment_method_account_id')
                ->nullable()
                ->constrained('payment_method_details');
        });
    }

    public function down()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'payment_method_account_id')) {
                $table->dropForeign(['payment_method_account_id']);
                $table->dropColumn('payment_method_account_id');
            }

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('payment_method_details');
        });
    }
}
