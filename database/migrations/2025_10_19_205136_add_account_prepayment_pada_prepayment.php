<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountPrepaymentPadaPrepayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('prepayments', function (Blueprint $table) {
            $table->foreignId('account_prepayment')
                ->nullable()->after('account_id')
                ->constrained('chart_of_accounts');
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
        Schema::table('prepayments', function (Blueprint $table) {
            $table->dropForeign('account_prepayment');
            $table->dropColumn('account_prepayment');
        });
    }
}
