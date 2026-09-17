<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKreditPajaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kredit_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('tahun')->nullable();                // tahun pajak, misalnya 2025
            $table->decimal('pph_22', 15, 2)->default(0); // total PPh 22
            $table->decimal('pph_23', 15, 2)->default(0); // total PPh 23
            $table->decimal('pph_24', 15, 2)->default(0); // total PPh 24

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
        Schema::dropIfExists('kredit_pajaks');
    }
}
