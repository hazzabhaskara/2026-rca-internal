<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeOffDay extends Model
{
    //
    protected $fillable = ['employee_id', 'tanggal', 'catatan'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
