<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagingJournalEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staging_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->nullable()->index();
            $table->string('source')->nullable();
            $table->string('tanggal')->nullable();
            $table->string('departemen')->nullable();
            $table->string('kode_akun')->nullable();
            $table->string('nama_akun')->nullable();
            $table->string('debit')->nullable();
            $table->string('kredit')->nullable();
            $table->text('comment_line')->nullable();
            $table->string('specpose')->nullable();
            $table->string('status')->default('pending'); // pending, success, failed
            $table->text('error_message')->nullable();
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
        Schema::dropIfExists('staging_journal_entries');
    }
}
