<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiscalDiJournalEntryDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('journal_entry_details', function (Blueprint $table) {
            // Jenis penyesuaian fiskal (select)
            $table->string('penyesuaian_fiskal')
                ->nullable();

            // Kode fiscal (diisi user manual)
            $table->string('kode_fiscal')
                ->nullable();
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
    }
}
