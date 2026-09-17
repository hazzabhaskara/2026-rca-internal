<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemAssembliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_assemblies', function (Blueprint $table) {
            $table->id();
            $table->date('date');

            // Produk hasil assembly (parent item)
            $table->unsignedBigInteger('parent_item_id');
            $table->foreign('parent_item_id')
                ->references('id')->on('items')
                ->onDelete('cascade');

            $table->integer('qty_built'); // jumlah produk yang dihasilkan
            $table->decimal('total_cost', 20, 2)->default(0); // total biaya dari komponen

            $table->string('status')->default('draft'); // draft, posted, cancelled
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('item_assemblies');
    }
}
