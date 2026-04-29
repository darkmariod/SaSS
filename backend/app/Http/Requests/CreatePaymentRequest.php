<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'reservation_id' => 'required|exists:reservations,id',
            'payment_method_code' => 'required|exists:payment_methods,code',
        ];
    }

    public function messages()
    {
        return [
            'reservation_id.required' => 'La reserva es obligatoria',
            'payment_method_code.required' => 'El medio de pago es obligatorio',
            'payment_method_code.exists' => 'Medio de pago no válido',
        ];
    }
}
