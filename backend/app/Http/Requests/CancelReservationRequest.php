<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cancellation_reason' => 'required|string|min:10|max:500',
        ];
    }
}
