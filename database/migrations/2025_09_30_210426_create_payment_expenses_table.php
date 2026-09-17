<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('source');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->foreignId('from_account_id')->constrained('chart_of_accounts');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_expenses');
    }
}
