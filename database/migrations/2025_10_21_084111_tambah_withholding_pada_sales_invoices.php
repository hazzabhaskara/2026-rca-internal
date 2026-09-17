<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahWithholdingPadaSalesInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('withholding_tax')
                ->nullable()->after('jenis_pembayaran_id')
                ->constrained('sales_taxes');
            $table->decimal('withholding_value', 15, 2)->after('withholding_tax');
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
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'withholding_tax')) {
                $table->dropForeign(['withholding_tax']);
                $table->dropColumn('withholding_tax');
            }

            // Hapus kolom withholding_value hanya kalau masih ada
            if (Schema::hasColumn('sales_invoices', 'withholding_value')) {
                $table->dropColumn('withholding_value');
            }
        });
    }
}
