<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'currency_name', 
        'currency_symbol',
        'flag',
        'buy_value',
        'sell_value',
        'branch_id'
    ];

}
