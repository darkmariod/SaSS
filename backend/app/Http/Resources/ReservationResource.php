<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->nombres . ' ' . $this->client->apellidos,
            ],
            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ],
            'professional' => [
                'id' => $this->professional->id,
                'name' => $this->professional->name,
            ],
            'reservation_date' => $this->reservation_date->format('Y-m-d'),
            'start_time' => $this->start_time->format('H:i'),
            'end_time' => $this->end_time->format('H:i'),
            'reservation_status' => $this->reservation_status,
            'payment_status' => $this->payment_status,
            'total_amount' => $this->total_amount,
            'items' => ReservationItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
