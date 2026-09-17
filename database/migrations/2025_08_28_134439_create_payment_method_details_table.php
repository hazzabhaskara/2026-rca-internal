<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_method_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_method_id'); // relasi ke payment_methods
            $table->unsignedBigInteger('account_id');            // relasi ke chart_of_accounts (coa)
            $table->string('deskripsi')->nullable();         // opsional, untuk catatan khusus
            $table->boolean('is_default')->default(0);       // penanda jika coa ini default
            $table->timestamps();

            // foreign key
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_method_details');
    }
}
