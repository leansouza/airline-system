<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'class_flights_id',
        'visitor_id',
        'ticket_number',
        'passenger_name',
        'passenger_cpf',
        'passenger_birthdate',
        'total_price',
        'has_baggage',
        'baggage_number',
        'status',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function classFlight()
    {
        return $this->belongsTo(ClassFlight::class, 'class_flights_id');
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}