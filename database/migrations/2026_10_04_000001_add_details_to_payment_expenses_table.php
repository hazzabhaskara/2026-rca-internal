<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToPaymentExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->unsignedBigInteger('departemen_id')->nullable()->after('user_id');
            $table->string('voucher_number', 50)->nullable()->unique()->after('departemen_id');
            $table->string('category_source', 100)->nullable()->after('source');
            $table->string('attachment', 255)->nullable()->after('notes');
            $table->string('status', 30)->default('posted')->after('attachment');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('departemen_id')->references('id')->on('departements')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['departemen_id']);
            $table->dropColumn(['user_id', 'departemen_id', 'voucher_number', 'category_source', 'attachment', 'status']);
        });
    }
}
