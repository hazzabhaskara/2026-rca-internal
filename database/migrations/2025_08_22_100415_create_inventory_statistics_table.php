<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->string('location')->default('Primary location');
            $table->integer('ytd_transactions')->default(0);
            $table->decimal('ytd_units', 15, 2)->default(0);
            $table->decimal('ytd_amount', 15, 2)->default(0);
            $table->decimal('ytd_cogs', 15, 2)->default(0);

            $table->integer('last_year_transactions')->default(0);
            $table->decimal('last_year_units', 15, 2)->default(0);
            $table->decimal('last_year_amount', 15, 2)->default(0);
            $table->decimal('last_year_cogs', 15, 2)->default(0);

            $table->date('last_sale_date')->nullable();
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
        Schema::dropIfExists('inventory_statistics');
    }
}
