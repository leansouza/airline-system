<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaggageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'baggage_number' => $this->baggage_number,
        ];
    }
}
