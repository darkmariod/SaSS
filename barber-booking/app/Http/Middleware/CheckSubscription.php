<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Solo check para owners con barbería
        if (! $user->hasRole('owner')) {
            return $next($request);
        }

        $barberShop = $user->barberShop;

        if (! $barberShop) {
            return $next($request);
        }

        $subscription = $barberShop->subscription;

        // Nunca tiene suscripción → redirect a elegir plan
        if (! $subscription) {
            return redirect()->route('register.plan')
                ->with('warning', 'Seleccioná un plan para empezar.');
        }

        // Trial expirado
        if ($subscription->status === 'trial'
            && $subscription->trial_ends_at
            && Carbon::now()->greaterThan($subscription->trial_ends_at)) {
            $subscription->update(['status' => 'expired']);

            return redirect()->route('register.plan')
                ->with('error', 'Tu período de prueba terminó. Elegí un plan para continuar.');
        }

        // Suscripción expirada o cancelada
        if (in_array($subscription->status, ['expired', 'cancelled'])) {
            return redirect()->route('register.plan')
                ->with('error', 'Tu suscripción no está activa. Elegí un plan para continuar.');
        }

        return $next($request);
    }
}
