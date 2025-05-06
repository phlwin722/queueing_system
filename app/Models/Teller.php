<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teller extends Model
{
    protected $fillable = [
        'teller_firstname',
        'teller_lastname',
        'teller_username',
        'teller_password',
        'role',
        'type_id',
        'branch_id',
    ];
    public function types()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}