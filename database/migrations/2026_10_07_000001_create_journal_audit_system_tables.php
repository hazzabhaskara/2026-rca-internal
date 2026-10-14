<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalAuditSystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Kamus Indikator Akun (Rule Engine)
        Schema::create('kamus_indikator_akuns', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->index(); // UTILITAS, PEMELIHARAAN, KONSUMSI, ASET, MUTASI_INTERNAL, dll
            $table->string('nama_indikator');
            $table->text('keyword_pattern'); // Komma-separated atau regex keyword
            $table->enum('match_type', ['contains', 'exact', 'regex'])->default('contains');
            $table->string('sumber_dana_prefix', 50)->nullable()->index(); // misal BNI0365, MDR6215, dll
            $table->enum('arah_mutasi', ['debit', 'kredit', 'any'])->default('any');
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->string('target_kode_akun', 50)->index();
            $table->string('target_nama_akun')->nullable();
            $table->json('alternate_kode_akuns')->nullable();
            $table->enum('tingkat_kepastian', ['PASTI', 'AMBIGU_REVIEW', 'KONDISIONAL'])->default('PASTI');
            $table->text('penjelasan_aturan')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 2. Tabel Hasil Audit / Deteksi Anomali
        Schema::create('journal_audit_anomalies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_detail_id')->index();
            $table->unsignedBigInteger('journal_entry_id')->index();
            $table->string('voucher', 100)->nullable()->index();
            $table->date('tanggal')->nullable()->index();
            $table->unsignedBigInteger('departemen_id')->nullable()->index();
            $table->string('current_kode_akun', 50)->index();
            $table->string('current_nama_akun')->nullable();
            $table->string('recommended_kode_akun', 50)->nullable()->index();
            $table->string('recommended_nama_akun')->nullable();
            $table->json('alternate_options')->nullable();
            $table->decimal('debits', 15, 2)->default(0);
            $table->decimal('credits', 15, 2)->default(0);
            $table->text('comment')->nullable();
            $table->enum('status', ['TEPAT', 'SALAH_AKUN', 'AMBIGU_BIAS', 'POLA_BARU'])->index();
            $table->text('alasan_diagnosa')->nullable();
            $table->unsignedBigInteger('rule_id')->nullable()->index();
            $table->boolean('is_resolved')->default(false)->index();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->string('resolved_action', 50)->nullable(); // 'reassigned', 'ignored', 'confirmed_valid'
            $table->timestamps();

            $table->foreign('journal_entry_detail_id')
                ->references('id')->on('journal_entry_details')
                ->onDelete('cascade');
            $table->foreign('journal_entry_id')
                ->references('id')->on('journal_entries')
                ->onDelete('cascade');
        });

        // 3. Tabel Log Remediasi / Re-assign Akun
        Schema::create('journal_audit_remediation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_detail_id')->index();
            $table->unsignedBigInteger('journal_audit_anomaly_id')->nullable()->index();
            $table->string('voucher', 100)->nullable();
            $table->string('old_kode_akun', 50);
            $table->string('new_kode_akun', 50);
            $table->unsignedBigInteger('old_departemen_akun_id')->nullable();
            $table->unsignedBigInteger('new_departemen_akun_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('comment')->nullable();
            $table->text('alasan')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('batch_id', 64)->nullable()->index();
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
        Schema::dropIfExists('journal_audit_remediation_logs');
        Schema::dropIfExists('journal_audit_anomalies');
        Schema::dropIfExists('kamus_indikator_akuns');
    }
}
