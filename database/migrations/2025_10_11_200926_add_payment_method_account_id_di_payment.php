<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodAccountIdDiPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_method_account_id')->nullable()->constrained('payment_method_details');
            $table->dropColumn('from_account');
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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payment_method_account_id');
            $table->dropColumn('payment_method_account_id');
            $table->string('from_account');
        });
    }
}
