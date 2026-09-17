<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StagingJournalEntry extends Model
{
    protected $table = 'staging_journal_entries';

    protected $fillable = [
        'batch_id',
        'source',
        'tanggal',
        'departemen',
        'kode_akun',
        'nama_akun',
        'debit',
        'kredit',
        'comment_line',
        'specpose',
        'status',
        'error_message'
    ];
}
