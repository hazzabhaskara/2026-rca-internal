<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkedAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linked_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('modul'); // contoh: 'setup', 'sales', 'purchase', dst
            $table->string('kode');  // contoh: 'retained_earnings', 'account_receivable'
            $table->unsignedBigInteger('akun_id'); // FK ke chart_of_accounts
            $table->timestamps();
            $table->foreign('akun_id')->references('id')->on('chart_of_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('linked_accounts');
    }
}
