<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilitySlotResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'available' => $this->available,
            'service' => $this->when(isset($this->service), $this->service),
        ];
    }
}
