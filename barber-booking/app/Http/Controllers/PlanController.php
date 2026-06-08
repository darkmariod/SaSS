<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function select(): Response
    {
        $user = auth()->user();
        $barberShop = $user->barberShop;
        $currentSubscription = null;
        $daysRemaining = null;

        if ($barberShop) {
            $currentSubscription = $barberShop->subscription;

            if ($currentSubscription && $currentSubscription->status === 'trial') {
                $daysRemaining = $currentSubscription->daysRemainingInTrial();
            }
        }

        return Inertia::render('Auth/SelectPlan', [
            'plans' => Plan::active()->get(),
            'currentSubscription' => $currentSubscription,
            'daysRemaining' => $daysRemaining,
            'barberShop' => $barberShop,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:plans,slug',
        ]);

        $plan = Plan::where('slug', $validated['plan_slug'])->firstOrFail();
        $user = auth()->user();
        $barberShop = $user->barberShop;

        if (! $barberShop) {
            return redirect()->route('register')
                ->with('error', 'Primero creá tu barbería.');
        }

        // Si ya tiene una suscripción activa, la cancelamos
        $existingSubscription = $barberShop->subscription;
        if ($existingSubscription && $existingSubscription->isActive()) {
            $existingSubscription->update([
                'status' => 'cancelled',
                'ends_at' => Carbon::now(),
                'cancelled_at' => Carbon::now(),
            ]);
        }

        // Crear nueva suscripción con trial
        $trialDays = $plan->trial_days;
        $barberShop->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => Carbon::now()->addDays($trialDays),
            'starts_at' => Carbon::now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', "¡Plan {$plan->name} activado! Disfrutá de {$trialDays} días de prueba gratis.");
    }
}
