<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetWindowSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_reset',
        'reset_minutes',
        'reset_days',
        'reset_weeks',
        'last_reset_at'
    ];

    protected $casts = [
        'auto_reset' => 'boolean',
        'reset_minutes' => 'integer',
        'reset_days' => 'integer',
        'reset_weeks' => 'integer',
        'last_reset_at' => 'datetime',
    ];
}
