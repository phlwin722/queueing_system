<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'rating', 'ease_of_use', 'waiting_time_satisfaction', 'suggestions'];
}
