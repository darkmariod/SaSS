<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CotizacionController extends Controller
{
    public function index()
    {
        return response()->json(
            Cotizacion::with('cliente')->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'quote_number' => ['required', 'string', 'max:50', 'unique:cotizaciones,quote_number'],
            'event_type' => ['nullable', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $cotizacion = Cotizacion::create($data)->load('cliente');

        return response()->json($cotizacion, 201);
    }

    public function show(Cotizacion $cotizacione)
    {
        return response()->json($cotizacione->load(['cliente', 'eventos', 'pagos']));
    }

    public function update(Request $request, Cotizacion $cotizacione)
    {
        $data = $request->validate([
            'cliente_id' => ['sometimes', 'required', 'exists:clientes,id'],
            'quote_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('cotizaciones', 'quote_number')->ignore($cotizacione->id),
            ],
            'event_type' => ['nullable', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $cotizacione->update($data);

        return response()->json($cotizacione->load('cliente'));
    }

    public function destroy(Cotizacion $cotizacione)
    {
        $cotizacione->delete();

        return response()->json(['message' => 'Cotización eliminada.']);
    }
}
