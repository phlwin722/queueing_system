<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Window extends Model
{
    protected $fillable = [
        'window_name', 
        'teller_id', 
        'type_id',
        'status',
    ];

}
