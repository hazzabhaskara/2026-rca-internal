<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKreditPajakPph25sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kredit_pajak_pph25s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kredit_pajak_id')->constrained('kredit_pajaks')->onDelete('cascade');
            $table->string('bulan'); // Januari, Februari, dst
            $table->decimal('nilai', 15, 2)->default(0);
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
        Schema::dropIfExists('kredit_pajak_pph25s');
    }
}
