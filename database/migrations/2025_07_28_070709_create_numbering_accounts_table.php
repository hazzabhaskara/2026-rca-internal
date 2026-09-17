<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberingAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('numbering_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('nama_grup', ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban']);
            $table->unsignedTinyInteger('jumlah_digit'); // contoh: 5, 6, dst
            $table->string('nomor_akun_awal'); // contoh: 10000
            $table->string('nomor_akun_akhir'); // contoh: 19999
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
        Schema::dropIfExists('numbering_accounts');
    }
}
