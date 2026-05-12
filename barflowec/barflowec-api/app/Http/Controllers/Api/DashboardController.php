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
        $propuestasCount = Cotizacion::count();

        $eventosActivosCount = Evento::whereIn('status', [
            'programado',
            'confirmado',
            'en_proceso',
        ])->count();

        $ingresosCobrados = Pago::where('status', 'pagado')->sum('amount');

        $saldoPendiente = Cotizacion::with('pagos')
            ->get()
            ->sum(function (Cotizacion $cotizacion) {
                $pagado = $cotizacion->pagos
                    ->where('status', 'pagado')
                    ->sum('amount');

                return max(((float) $cotizacion->total) - ((float) $pagado), 0);
            });

        $cobrosPendientesCount = Cotizacion::with('pagos')
            ->get()
            ->filter(function (Cotizacion $cotizacion) {
                $pagado = $cotizacion->pagos
                    ->where('status', 'pagado')
                    ->sum('amount');

                return (((float) $cotizacion->total) - ((float) $pagado)) > 0;
            })
            ->count();

        $recentQuotes = Cotizacion::with(['cliente', 'pagos'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Cotizacion $cotizacion) {
                $paid = $cotizacion->pagos
                    ->where('status', 'pagado')
                    ->sum('amount');

                $balance = max(((float) $cotizacion->total) - ((float) $paid), 0);

                return [
                    'id' => $cotizacion->id,
                    'code' => $cotizacion->quote_number,
                    'client' => $cotizacion->cliente?->name ?? 'Sin cliente',
                    'event_type' => $cotizacion->event_type ?? '-',
                    'amount' => (float) $cotizacion->total,
                    'paid' => (float) $paid,
                    'balance' => (float) $balance,
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
                    'responsible' => $evento->bartender_name ?? 'Por asignar',
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
                    'label' => 'Propuestas',
                    'value' => $propuestasCount,
                    'change' => 'comerciales',
                    'color' => 'green',
                ],
                [
                    'label' => 'Eventos en agenda',
                    'value' => $eventosActivosCount,
                    'change' => 'activos',
                    'color' => 'yellow',
                ],
                [
                    'label' => 'Saldos pendientes',
                    'value' => $cobrosPendientesCount,
                    'change' => '$' . number_format($saldoPendiente, 2),
                    'color' => 'red',
                ],
            ],
            'income' => [
                'paid' => (float) $ingresosCobrados,
                'pending' => (float) $saldoPendiente,
            ],
            'recent_quotes' => $recentQuotes,
            'upcoming_events' => $upcomingEvents,
        ]);
    }
}
