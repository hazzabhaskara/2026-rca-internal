<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_pembayaran_id')->constrained('payment_methods');
            $table->string('from_account');
            $table->string('source')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->date('payment_date');
            $table->text('comment')->nullable();
            $table->enum('type', ['invoice', 'other']); // invoice payment atau 
            $table->timestamps();


            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
