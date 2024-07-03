<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassFlightResource extends JsonResource
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
            'flight' => new FlightResource($this->whenLoaded('flight')),
            'class_type' => $this->class_type,
            'seat_quantity' => $this->seat_quantity,
            'price' => $this->price,
        ];
    }
}
