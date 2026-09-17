<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun')->unique();
            $table->string('nama_akun');
            $table->enum('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban']);
            $table->string('level_akun')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('aktif')->default(true);
            $table->boolean('omit_zero_balance')->default(false);
            $table->boolean('allow_project_allocation')->default(false);
            $table->text('catatan')->nullable();
            $table->text('catatan_pajak')->nullable();
            $table->unsignedBigInteger('klasifikasi_id')->nullable();
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chart_of_accounts');
    }
}
