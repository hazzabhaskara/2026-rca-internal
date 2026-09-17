<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentIdToPrepaymentAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prepayment_allocations', function (Blueprint $table) {
            // Tambahkan kolom payment_id setelah kolom id (atau di posisi yang kamu inginkan)
            $table->foreignId('payment_id')
                ->nullable()
                ->after('id')
                ->constrained('payments')
                ->onDelete('cascade');
            // "cascade" agar jika payment dihapus, alokasi ikut terhapus
        });
    }

    /**
     * Reverse migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prepayment_allocations', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
}
