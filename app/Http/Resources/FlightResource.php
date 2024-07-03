<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'flight_number' => $this->flight_number,
            'departure_time' => $this->departure_time,
            'origin_airport' => new AirportResource($this->whenLoaded('originAirport')),
            'destination_airport' => new AirportResource($this->whenLoaded('destinationAirport')),
            'classes' => ClassFlightResource::collection($this->whenLoaded('classes')),
        ];
    }
}
