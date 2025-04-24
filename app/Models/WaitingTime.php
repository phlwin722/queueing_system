<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingTime extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'Waiting_time',
        'branch_id'
    ];

    public function getFormattedTime()
    {
        $minutes = floor($this->Waiting_time / 60);
        $seconds = $this->Waiting_time % 60;
        return sprintf('%02:%02d', $minutes, $seconds);
    }
}
