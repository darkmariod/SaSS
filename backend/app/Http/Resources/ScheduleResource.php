<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'professional_id' => $this->professional_id,
            'branch_id' => $this->branch_id,
            'branch_name' => $this->whenLoaded('branch', fn() => $this->branch->name),
            'day_of_week' => $this->day_of_week,
            'day_name' => $this->day_of_week !== null ? 
                ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][$this->day_of_week] : 
                'Todos los días',
            'start_time' => $this->start_time->format('H:i'),
            'end_time' => $this->end_time->format('H:i'),
            'is_active' => $this->is_active,
        ];
    }
}
