<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //

    protected $fillable = [
        'nama_project',
        'start_date',
        'end_date',
        'revenue',
        'expens',
        'status',
    ];
}
