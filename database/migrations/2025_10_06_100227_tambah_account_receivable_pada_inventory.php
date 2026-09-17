<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahAccountReceivablePadaInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('item_accounts', function (Blueprint $table) {
            $table->foreignId('account_receivable_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_accounts', function (Blueprint $table) {
            // Hapus constraint foreign key
            $table->dropForeign(['account_receivable_id']);

            // Baru hapus kolom
            $table->dropColumn('account_receivable_id');
        });
    }
}
