<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Auth middleware handles this
    }

    public function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'professional_id' => 'required|exists:professionals,id',
            'service_id' => 'required|exists:services,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
        ];
    }

    public function messages()
    {
        return [
            'branch_id.required' => 'La sede es obligatoria',
            'professional_id.required' => 'El profesional es obligatorio',
            'service_id.required' => 'El servicio es obligatorio',
            'reservation_date.required' => 'La fecha es obligatoria',
            'reservation_date.after_or_equal' => 'No puedes reservar fechas pasadas',
            'start_time.required' => 'La hora es obligatoria',
            'start_time.date_format' => 'Formato de hora inválido (HH:MM)',
        ];
    }
}
