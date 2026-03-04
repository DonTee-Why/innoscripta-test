<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'last_name',
        'country',
        'salary',
        'ssn',
        'address',
        'tax_id',
        'goal',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
    ];

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->last_name;
    }
}
