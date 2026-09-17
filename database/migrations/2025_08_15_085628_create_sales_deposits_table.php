<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_no');
            $table->unsignedBigInteger('jenis_pembayaran_id');
            $table->unsignedBigInteger('account_id');
            $table->date('deposit_date');
            $table->unsignedBigInteger('customer_id');
            $table->string('deposit_reference')->nullable();
            $table->decimal('deposit_amount', 15, 2);
            $table->text('comment')->nullable();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('jenis_pembayaran_id')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
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
        Schema::dropIfExists('sales_deposits');
    }
}
