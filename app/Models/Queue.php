<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{

    protected $fillable = [
        'id',
        'token',
        'name', 
        'email', 
        'email_status',
        'type_id',
        'teller_id',
        'queue_number', 
        'status',
        'waiting_customer'
    ];
}
