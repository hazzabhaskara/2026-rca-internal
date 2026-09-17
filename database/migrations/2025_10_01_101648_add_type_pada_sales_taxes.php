<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypePadaSalesTaxes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sales_taxes', function (Blueprint $table) {
            $table->enum('type', ['input_tax', 'withholding_tax'])->default('input_tax');
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
        Schema::table('sales_taxes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
