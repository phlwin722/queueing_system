<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPSTORM_META\map;

class Manager extends Model
{
    use HasFactory;
    protected $fillable = [
        'manager_firstname',
        'manager_lastname',
        'manager_username',
        'manager_password',
        'manager_status',
        'branch_id',
        'Image'
    ];
}
