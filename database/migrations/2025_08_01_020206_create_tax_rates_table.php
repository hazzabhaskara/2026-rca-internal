<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ptkp_id')->constrained('ptkps')->onDelete('cascade');
            $table->decimal('min_penghasilan', 15, 2);
            $table->decimal('max_penghasilan', 15, 2);
            $table->decimal('tarif_ter', 5, 2); // Dalam persen, contoh: 1.25
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
        Schema::dropIfExists('tax_rates');
    }
}
