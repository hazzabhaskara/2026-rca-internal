<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->string('invoice_number')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('original_amount', 15, 2)->default(0);
            $table->decimal('amount_owing', 15, 2)->default(0);
            $table->decimal('discount_available', 15, 2)->nullable();
            $table->decimal('discount_taken', 15, 2)->nullable();
            $table->decimal('payment_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('description')->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->string('allocation')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_details');
    }
}
