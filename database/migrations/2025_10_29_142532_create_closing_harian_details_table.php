<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClosingHarianDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('closing_harian_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('closing_harian_id');
            $table->foreign('closing_harian_id')
                ->references('id')
                ->on('closing_harians')
                ->onDelete('cascade');
            $table->foreignId('wahana_item_id')->constrained('wahana_items')->onDelete('cascade');
            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('harga', 18, 2)->default(0);
            $table->decimal('jumlah', 18, 2)->default(0);
            $table->decimal('omset_total', 18, 2)->default(0);
            $table->decimal('qris', 18, 2)->default(0);
            $table->decimal('cash', 18, 2)->default(0);
            $table->decimal('merch', 18, 2)->default(0);
            $table->decimal('rca', 18, 2)->default(0);
            $table->decimal('titipan', 18, 2)->default(0);
            $table->decimal('lebih_kurang', 18, 2)->default(0);
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
        Schema::dropIfExists('closing_harian_details');
    }
}
