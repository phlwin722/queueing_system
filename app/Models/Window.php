<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Window extends Model
{
    protected $fillable = ['window_name', 'teller_id', 'type_id'];
// 🔹 Dapat may 'tellers_id' at 'types_id'

    // 🔹 Relationship sa Teller
    public function teller()
    {
        return $this->belongsTo(Teller::class, 'teller_id', 'id');
    }

    // 🔹 Relationship sa Type
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }
}