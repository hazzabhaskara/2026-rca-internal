<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiscalAccountPadaChartOfAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreignId('fiscal_account_id')->after('klasifikasi_id')->nullable()->constrained('fiscal_accounts');
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
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropForeign('fiscal_account_id');
            $table->dropColumn('fiscal_account_id');
        });
    }
}
