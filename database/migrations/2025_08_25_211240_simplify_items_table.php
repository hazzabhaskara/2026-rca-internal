<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SimplifyItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('items', function (Blueprint $table) {
            // Hapus foreign key constraint dulu
            $table->dropForeign(['account_id']);
            $table->dropForeign(['category_id']); // kalau ada juga di tabelmu

            // Baru drop kolom2 lama
            $table->dropColumn([
                'item_name',
                'unit',
                'base_price',
                'tax_rate',
                'account_id',
                'is_active',
                'category_id',
                'brand',
                'stock_quantity',
                'purchase_price',
                'barcode',
                'image',
            ]);

            // Tambah kolom type
            $table->enum('type', ['inventory', 'service'])->after('item_description');
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
        Schema::table('items', function (Blueprint $table) {
            $table->string('item_name')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('base_price', 15, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts');
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->nullable()->constrained('item_categories');
            $table->string('brand')->nullable();
            $table->decimal('stock_quantity', 15, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->string('barcode')->nullable();
            $table->text('image')->nullable();
        });
    }
}
