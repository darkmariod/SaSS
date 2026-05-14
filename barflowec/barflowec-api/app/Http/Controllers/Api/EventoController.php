<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        return response()->json(
            Evento::with(['cliente', 'cotizacion'])->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'cotizacion_id' => ['nullable', 'exists:cotizaciones,id'],
            'name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'bartender_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $evento = Evento::create($data)->load(['cliente', 'cotizacion']);

        return response()->json($evento, 201);
    }

    public function show(Evento $evento)
    {
        return response()->json($evento->load(['cliente', 'cotizacion', 'pagos']));
    }

    public function update(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'cliente_id' => ['sometimes', 'required', 'exists:clientes,id'],
            'cotizacion_id' => ['nullable', 'exists:cotizaciones,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'event_date' => ['sometimes', 'required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'bartender_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $evento->update($data);

        return response()->json($evento->load(['cliente', 'cotizacion']));
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();

        return response()->json(['message' => 'Evento eliminado.']);
    }
}
