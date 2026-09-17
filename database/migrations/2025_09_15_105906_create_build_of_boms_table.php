<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildOfBomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('build_of_boms', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('item_id'); // produk jadi
            $table->integer('qty_to_build');
            $table->decimal('total_cost', 20, 2)->default(0);

            $table->string('status')->default('draft');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('build_of_boms');
    }
}
