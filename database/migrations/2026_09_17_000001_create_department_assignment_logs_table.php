<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentAssignmentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_detail_id')
                ->constrained('journal_entry_details')
                ->onDelete('cascade');
            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->onDelete('cascade');
            $table->string('voucher')->nullable()->index();
            $table->string('kode_akun')->nullable()->index();
            $table->foreignId('old_departemen_id')
                ->nullable()
                ->constrained('departements')
                ->onDelete('set null');
            $table->foreignId('new_departemen_id')
                ->nullable()
                ->constrained('departements')
                ->onDelete('set null');
            $table->unsignedBigInteger('old_departemen_akun_id')->nullable()->index();
            $table->unsignedBigInteger('new_departemen_akun_id')->nullable()->index();
            $table->string('action_type', 30)->index(); // 'reassign', 'unassign', 'initial_assign'
            $table->text('reason');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->string('batch_id', 64)->nullable()->index();
            $table->boolean('is_override_closed')->default(false);
            $table->string('ip_address', 45)->nullable();
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
        Schema::dropIfExists('department_assignment_logs');
    }
}
