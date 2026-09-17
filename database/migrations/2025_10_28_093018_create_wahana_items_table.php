<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWahanaItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wahana_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wahana_id')->constrained('wahanas')->onDelete('cascade');
            $table->string('kode_item');
            $table->string('nama_item');
            $table->decimal('harga', 15, 2);
            $table->boolean('status');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
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
        Schema::dropIfExists('wahana_items');
    }
}
