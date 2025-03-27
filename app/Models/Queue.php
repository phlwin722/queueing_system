<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{

    use HasFactory; // Add this line to use the factory feature
    protected $fillable = [
        'id',
        'token',
        'name', 
        'email', 
        'email_status',
        'type_id',
        'teller_id',
        'queue_number', 
        'position',
        'status',
        'waiting_customer',
        'currency_selected'
    ];
}
