<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationItemResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'service' => [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'duration_minutes' => $this->service->duration_minutes,
            ],
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
        ];
    }
}
