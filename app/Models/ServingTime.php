<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServingTime extends Model
{
    protected $fillable = [
        'minutes', 
        'type_id', 
        'teller_id'
    ];
}
