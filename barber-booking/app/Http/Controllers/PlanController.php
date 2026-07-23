<?php

namespace App\Http\Controllers;

use App\Models\Plan;
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
        $currentSubscription = $barberShop?->subscription;
        $daysRemaining = null;

        if ($currentSubscription && $currentSubscription->status === 'trial') {
            $daysRemaining = $currentSubscription->daysRemainingInTrial();
        }

        return Inertia::render('Auth/SelectPlan', [
            'plans' => Plan::active()->get()->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'slug' => $plan->slug,
                'name' => $plan->name,
                'description' => $plan->description,
                'max_barbers' => $plan->max_barbers,
                'setup_price' => $plan->setup_price,
                'monthly_price' => $plan->priceFor('monthly'),
                'annual_price' => $plan->priceFor('annual'),
                'trial_days' => $plan->trial_days,
            ]),
            'currentSubscription' => $currentSubscription,
            'daysRemaining' => $daysRemaining,
            'barberShop' => $barberShop,
            'paymentInfo' => config('billing'),
        ]);
    }

    /**
     * Register a subscription payment (owner → platform) for review. The payment
     * lands as "pending"; a super_admin confirms it, which activates the plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:plans,slug',
            'billing_period' => 'required|in:monthly,annual',
            'reference' => 'nullable|string|max:255',
        ]);

        $plan = Plan::where('slug', $validated['plan_slug'])->firstOrFail();
        $barberShop = auth()->user()->barberShop;

        if (! $barberShop) {
            return redirect()->route('register')
                ->with('error', 'Primero creá tu barbería.');
        }

        $barberShop->subscriptionPayments()->create([
            'subscription_id' => $barberShop->subscription?->id,
            'plan_id' => $plan->id,
            'billing_period' => $validated['billing_period'],
            'amount' => $plan->priceFor($validated['billing_period']),
            'status' => 'pending',
            'reference' => $validated['reference'] ?? null,
        ]);

        return redirect()->route('register.plan')
            ->with('success', 'Registramos tu pago. En cuanto lo confirmemos, se activa tu plan. ¡Gracias!');
    }
}
