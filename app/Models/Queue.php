<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{

    protected $fillable = [
        'name', 
        'email', 
        'queue_number', 
        'status',
        'waiting_customer'
    ];
}
