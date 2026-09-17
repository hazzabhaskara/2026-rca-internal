<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentExpenseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_expense_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_expense_id')->constrained('payment_expenses')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->text('deskripsi');
            $table->decimal('amount', 15, 2);
            $table->decimal('tax', 15, 2);
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
        Schema::dropIfExists('payment_expense_details');
    }
}
