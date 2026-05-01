<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getTerCategoryAttribute()
    {
        $status = strtoupper($this->ptkp_status);
        
        $catA = ['TK/0', 'TK/1', 'K/0'];
        $catB = ['TK/2', 'TK/3', 'K/1', 'K/2'];
        $catC = ['K/3'];
        
        if (in_array($status, $catA)) return 'A';
        if (in_array($status, $catB)) return 'B';
        if (in_array($status, $catC)) return 'C';
        
        return 'A'; // default
    }

    protected static function booted()
    {
        static::saving(function ($employee) {
            $employee->ter_category = $employee->ter_category; // trigger mutator above
        });
    }
}
