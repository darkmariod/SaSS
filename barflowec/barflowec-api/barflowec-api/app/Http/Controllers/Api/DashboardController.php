<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Evento;
use App\Models\Pago;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $clientesCount = Cliente::count();
        $cotizacionesCount = Cotizacion::count();

        $eventosActivosCount = Evento::whereIn('status', [
            'programado',
            'confirmado',
            'en_proceso',
        ])->count();

        $pagosPendientesCount = Pago::where('status', 'pendiente')->count();
        $pagosPendientesTotal = Pago::where('status', 'pendiente')->sum('amount');
        $ingresosPagados = Pago::where('status', 'pagado')->sum('amount');

        $recentQuotes = Cotizacion::with('cliente')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Cotizacion $cotizacion) {
                return [
                    'id' => $cotizacion->id,
                    'code' => $cotizacion->quote_number,
                    'client' => $cotizacion->cliente?->name ?? 'Sin cliente',
                    'event_type' => $cotizacion->event_type ?? '-',
                    'amount' => (float) $cotizacion->total,
                    'status' => $cotizacion->status,
                    'created_at' => $cotizacion->created_at?->format('Y-m-d'),
                ];
            });

        $upcomingEvents = Evento::query()
            ->whereDate('event_date', '>=', Carbon::today())
            ->orderBy('event_date')
            ->take(5)
            ->get()
            ->map(function (Evento $evento) {
                return [
                    'name' => $evento->name,
                    'date' => $evento->event_date,
                    'location' => $evento->location ?? '-',
                    'bartender' => $evento->bartender_name ?? 'Por asignar',
                ];
            });

        return response()->json([
            'metrics' => [
                [
                    'label' => 'Clientes',
                    'value' => $clientesCount,
                    'change' => 'registrados',
                    'color' => 'purple',
                ],
                [
                    'label' => 'Cotizaciones',
                    'value' => $cotizacionesCount,
                    'change' => 'totales',
                    'color' => 'green',
                ],
                [
                    'label' => 'Eventos activos',
                    'value' => $eventosActivosCount,
                    'change' => 'programados',
                    'color' => 'yellow',
                ],
                [
                    'label' => 'Pagos pendientes',
                    'value' => $pagosPendientesCount,
                    'change' => '$' . number_format($pagosPendientesTotal, 2),
                    'color' => 'red',
                ],
            ],
            'income' => [
                'paid' => (float) $ingresosPagados,
                'pending' => (float) $pagosPendientesTotal,
            ],
            'recent_quotes' => $recentQuotes,
            'upcoming_events' => $upcomingEvents,
        ]);
    }
}
