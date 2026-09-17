<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupUnit extends Model
{
    //
    protected $fillable =
    ['nama', 'deskripsi'];

    public function unitKerja()
    {
        return $this->hasMany(UnitKerja::class);
    }
}
