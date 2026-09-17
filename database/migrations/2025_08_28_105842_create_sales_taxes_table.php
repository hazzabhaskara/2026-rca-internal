<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();              // nama/label pajak
            // Akun pelacakan (opsional -> nullable)
            $table->foreignId('purchase_account_id')->nullable()
                ->constrained('chart_of_accounts')->nullOnDelete(); // "Acct. to track tax paid on purchases"
            $table->foreignId('sales_account_id')->nullable()
                ->constrained('chart_of_accounts')->nullOnDelete();  // "Acct. to track tax charged on sales"
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('sales_taxes');
    }
}
