<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTabelClosingHarians extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('closing_harians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->cascadeOnDelete();
            $table->decimal('total_omset', 18, 2)->default(0);
            $table->decimal('total_qris', 18, 2)->default(0);
            $table->decimal('total_cash', 18, 2)->default(0);
            $table->decimal('total_merch', 18, 2)->default(0);
            $table->decimal('total_rca', 18, 2)->default(0);
            $table->decimal('total_titipan', 18, 2)->default(0);
            $table->decimal('mdr_rate', 5, 2)->default(0); // 0.7%
            $table->decimal('mdr_amount', 18, 2)->default(0);
            $table->decimal('subtotal_after_mdr', 18, 2)->default(0);
            $table->decimal('total_lebih_kurang', 18, 2)->default(0);
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
        //
        Schema::dropIfExists('closing_harians');
    }
}
