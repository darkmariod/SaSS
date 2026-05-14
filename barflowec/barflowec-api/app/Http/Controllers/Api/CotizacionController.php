<?php

namespace App\Http\Controllers\Api;

use App\Exports\CotizacionExport;
use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
            'subtotal' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $data = $this->applyTotals($data);

        return response()->json(Cotizacion::create($data)->load('cliente'), 201);
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
            'subtotal' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        if (array_key_exists('subtotal', $data)) {
            $data = $this->applyTotals($data);
        }

        $cotizacione->update($data);

        return response()->json($cotizacione->load('cliente'));
    }

    public function destroy(Cotizacion $cotizacione)
    {
        $cotizacione->delete();

        return response()->json(['message' => 'Propuesta eliminada.']);
    }

    public function export(Cotizacion $cotizacion)
    {
        $cotizacion->load('cliente');

        $clientName = Str::slug($cotizacion->cliente?->name ?? 'cliente');
        $fileName = "{$cotizacion->quote_number}-{$clientName}.xlsx";

        return Excel::download(new CotizacionExport($cotizacion), $fileName);
    }

    private function applyTotals(array $data): array
    {
        $subtotalCents = $this->decimalToCents((string) ($data['subtotal'] ?? '0'));
        $taxCents = (int) round($subtotalCents * 0.12);
        $totalCents = $subtotalCents + $taxCents;

        $data['subtotal'] = $this->centsToDecimal($subtotalCents);
        $data['tax'] = $this->centsToDecimal($taxCents);
        $data['total'] = $this->centsToDecimal($totalCents);

        return $data;
    }

    private function decimalToCents(string $value): int
    {
        $normalized = str_replace(',', '.', trim($value));
        $parts = explode('.', $normalized, 2);

        $whole = preg_replace('/[^0-9]/', '', $parts[0] ?? '0');
        $decimal = preg_replace('/[^0-9]/', '', $parts[1] ?? '');
        $decimal = substr(str_pad($decimal, 3, '0'), 0, 3);

        $cents = ((int) ($whole ?: 0)) * 100;
        $centDecimals = (int) substr($decimal, 0, 2);
        $thirdDecimal = (int) substr($decimal, 2, 1);

        if ($thirdDecimal >= 5) {
            $centDecimals++;
        }

        return $cents + $centDecimals;
    }

    private function centsToDecimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
