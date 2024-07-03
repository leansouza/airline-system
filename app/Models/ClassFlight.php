<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassFlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'class_type',
        'seat_quantity',
        'price',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'class_flights_id');
    }
}