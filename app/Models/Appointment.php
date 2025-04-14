<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['branch_name', 'name', 'contact', 'service', 'branch', 'appointment_date', 'time', 'status'];

}
