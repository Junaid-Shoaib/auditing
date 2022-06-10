<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'joining_date', 'name', 'department', 'designation', 'gross_salary', 'medical', 'conveyance'
    ];
}
