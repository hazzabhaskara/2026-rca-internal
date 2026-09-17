<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepositAccountDiSalesDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sales_deposits', function (Blueprint $table) {
            $table->foreignId('account_deposit')
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
        Schema::table('sales_deposits', function (Blueprint $table) {
            $table->dropForeign('account_deposit');
            $table->dropColumn('account_deposit');
        });
    }
}
