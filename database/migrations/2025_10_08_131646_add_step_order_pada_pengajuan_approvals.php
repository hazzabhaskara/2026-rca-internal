<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStepOrderPadaPengajuanApprovals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('pengajuan_approvals', function (Blueprint $table) {
            $table->integer('step_order')->after('approval_step_id');
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
        Schema::table('pengajuan_approvals', function (Blueprint $table) {
            $table->dropColumn('step_order');
        });
    }
}
