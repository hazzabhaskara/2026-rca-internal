<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_number')->unique();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->string('unit');
            $table->decimal('base_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->boolean('is_active')->default(true);

            // Optional
            $table->foreignId('category_id')->nullable()->constrained('item_categories');
            $table->string('brand')->nullable();
            $table->decimal('stock_quantity', 15, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->string('barcode')->nullable();
            $table->text('image')->nullable();

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
        Schema::dropIfExists('items');
    }
}
