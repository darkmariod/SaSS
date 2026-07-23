<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the admin panel behind an active tenant subscription. An owner whose
 * trial or paid period has lapsed is sent to the plan page to renew, so the
 * subscription actually has teeth instead of being a passive record.
 *
 * Platform staff (admin / super_admin) are never gated, and non-owner staff
 * (barbers) are let through — the owner is the billing party.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasAnyRole(['admin', 'super_admin'])) {
            return $next($request);
        }

        if (! $user->hasRole('owner')) {
            return $next($request);
        }

        $subscription = $user->barberShop?->subscription;

        if ($subscription && $subscription->isActive() && ! $subscription->isExpired()) {
            return $next($request);
        }

        return redirect()->route('register.plan');
    }
}
