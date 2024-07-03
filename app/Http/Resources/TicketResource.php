<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'flight_id' => $this->flight_id,
            'class_flights_id' => $this->class_flights_id,
            'visitor_id' => $this->visitor_id,
            'ticket_number' => $this->ticket_number,
            'passenger_name' => $this->passenger_name,
            'passenger_cpf' => $this->passenger_cpf,
            'passenger_birthdate' => $this->passenger_birthdate,
            'total_price' => $this->total_price,
            'has_baggage' => $this->has_baggage,
            'baggage_number' => $this->baggage_number,
            'status' => $this->status,
        ];
    }
}