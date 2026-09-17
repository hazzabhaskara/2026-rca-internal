<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HapusKolomCustomerIdInPurchaseInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            //
            // hapus relasi lama
            if (Schema::hasColumn('purchase_invoices', 'customer_id')) {
                // Nama constraint biasanya "purchase_orders_customer_id_foreign"
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            }

            // tambahkan kolom baru dengan relasi
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('payment_method_details')
                ->after('jenis_pembayaran_id');

            $table->foreignId('vendor_id')
                ->nullable()
                ->constrained('vendors')
                ->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['account_id', 'vendor_id']);

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->after('jenis_pembayaran_id');
        });
    }
}
