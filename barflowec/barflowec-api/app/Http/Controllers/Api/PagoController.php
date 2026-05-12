<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index()
    {
        return response()->json(
            Pago::with(['cotizacion.cliente', 'evento'])->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cotizacion_id' => ['nullable', 'exists:cotizaciones,id'],
            'evento_id' => ['nullable', 'exists:eventos,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $pago = Pago::create($data)->load(['cotizacion.cliente', 'evento']);

        return response()->json($pago, 201);
    }

    public function show(Pago $pago)
    {
        return response()->json($pago->load(['cotizacion.cliente', 'evento']));
    }

    public function update(Request $request, Pago $pago)
    {
        $data = $request->validate([
            'cotizacion_id' => ['nullable', 'exists:cotizaciones,id'],
            'evento_id' => ['nullable', 'exists:eventos,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $pago->update($data);

        return response()->json($pago->load(['cotizacion.cliente', 'evento']));
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();

        return response()->json(['message' => 'Pago eliminado.']);
    }
}
