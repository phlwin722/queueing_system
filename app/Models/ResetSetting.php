<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetSetting extends Model
{
    protected $table = 'reset_settings';

    protected $fillable = [
        'auto_reset',
        'reset_type',
        'reset_time',
        'reset_day',
        'reset_date',
    ];

    protected $casts = [
        'auto_reset' => 'boolean',
    ];
}
