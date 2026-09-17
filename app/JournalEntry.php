<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    //
    protected $fillable = [
        'source',
        'tanggal',
        'comment'
    ];
    public function details()
    {
        return $this->hasMany(JournalEntryDetail::class);
    }
}
