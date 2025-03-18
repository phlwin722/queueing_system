<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WindowArchive extends Model
{
    protected $fillable = [
        'window_name',
        'type_id',
        'teller_id',
        'archived_at',
    ];
    public $timestamps = true; 

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function teller()
    {
        return $this->belongsTo(Teller::class, 'teller_id');
    }
}
