<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_airport_id',
        'destination_airport_id',
        'flight_number',
        'departure_time',
    ];

    public function originAirport()
    {
        return $this->belongsTo(Airport::class, 'origin_airport_id');
    }

    public function destinationAirport()
    {
        return $this->belongsTo(Airport::class, 'destination_airport_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassFlight::class);
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}