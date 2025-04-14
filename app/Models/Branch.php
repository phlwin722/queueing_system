<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'branch_name',
        'manager_id',
        'region',
        'province',
        'city',
        'Barangay',
        'address',
        'status',
        'opening_date',
    ];
}
