<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesDiscountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_discount_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_discount_id')->constrained('sales_discounts')->onDelete('cascade');
            $table->integer('hari_ke')->nullable(); // Untuk early payment
            $table->enum('tipe_nilai', ['persen', 'nominal'])->default('persen');
            $table->decimal('nilai_diskon', 15, 2); // Bisa persen atau nominal
            $table->integer('urutan')->nullable(); // Untuk diskon berlapis
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
        Schema::dropIfExists('sales_discount_details');
    }
}
