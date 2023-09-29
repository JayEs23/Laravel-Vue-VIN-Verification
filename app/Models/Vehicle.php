<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vin',
        'make',
        'model',
        'year',
        'color',
        'salvage_data',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
