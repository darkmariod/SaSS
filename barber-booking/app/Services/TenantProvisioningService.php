<?php

namespace App\Services;

use App\Models\BarberAvailability;
use App\Models\BarberProfile;
use App\Models\BarberShop;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Provisions everything a new tenant (barbershop) needs on self-service signup:
 * roles, the shop, a ready-to-use starter catalog and a trial subscription.
 *
 * Every step is idempotent so a fresh production database (where the role/plan
 * seeders may not have run) never breaks registration — the service guarantees
 * its own prerequisites instead of assuming a prior seed.
 */
class TenantProvisioningService
{
    /**
     * Create a fully working tenant for an owner and return the shop.
     * Runs in a single transaction: a partial signup would leave an owner
     * without a usable shop.
     */
    public function provision(User $owner, string $shopName): BarberShop
    {
        return DB::transaction(function () use ($owner, $shopName): BarberShop {
            $this->ensureRolesExist();

            $owner->forceFill([
                'role_id' => Role::where('name', 'owner')->value('id'),
            ])->save();
            $owner->syncRoles('owner');

            $shop = BarberShop::create([
                'owner_id' => $owner->id,
                'name' => $shopName,
                'slug' => $this->uniqueSlug($shopName),
                'is_active' => true,
            ]);

            $this->seedStarterCatalog($shop, $owner);
            $this->startTrial($shop);

            return $shop;
        });
    }

    /**
     * Guarantee the four base roles exist as both the custom Role (role_id FK)
     * and the Spatie role used by HasRoles. Safe to call on every signup.
     */
    public function ensureRolesExist(): void
    {
        $roles = [
            'owner' => 'Dueño',
            'admin' => 'Administrador',
            'barber' => 'Barbero',
            'customer' => 'Cliente',
        ];

        foreach ($roles as $name => $displayName) {
            Role::firstOrCreate(['name' => $name], ['display_name' => $displayName, 'is_active' => true]);
            SpatieRole::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    /**
     * Seed a minimal but immediately bookable catalog so the owner can share
     * their public booking link right after signing up: one main service with
     * two priced options, the owner as the first barber, and weekly hours.
     */
    protected function seedStarterCatalog(BarberShop $shop, User $owner): void
    {
        $mainCut = Service::create([
            'barber_shop_id' => $shop->id,
            'name' => 'Corte de cabello',
            'category' => 'Hombre',
            'description' => 'Servicio principal de corte.',
            'service_type' => 'main',
            'parent_service_id' => null,
            'duration_minutes' => 15,
            'price' => 0.00,
            'requires_payment' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Service::create([
            'barber_shop_id' => $shop->id,
            'name' => 'Corte clásico',
            'category' => 'Hombre',
            'description' => 'Corte con máquina y tijera.',
            'service_type' => 'option',
            'parent_service_id' => $mainCut->id,
            'duration_minutes' => 30,
            'price' => 8.00,
            'requires_payment' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Service::create([
            'barber_shop_id' => $shop->id,
            'name' => 'Corte + barba',
            'category' => 'Hombre',
            'description' => 'Corte completo más arreglo de barba.',
            'service_type' => 'option',
            'parent_service_id' => $mainCut->id,
            'duration_minutes' => 45,
            'price' => 12.00,
            'requires_payment' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $profile = BarberProfile::create([
            'barber_shop_id' => $shop->id,
            'user_id' => $owner->id,
            'display_name' => $owner->name,
            'bio' => 'Barbero principal.',
            'commission_percentage' => 100.00,
            'is_active' => true,
        ]);

        // Monday–Saturday 09:00–18:00 by default; the owner tunes this later.
        foreach ([1, 2, 3, 4, 5, 6] as $day) {
            BarberAvailability::create([
                'barber_profile_id' => $profile->id,
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Start the tenant on the default plan's free trial. The plan is created if
     * missing so signup never depends on the plan seeder having run.
     */
    protected function startTrial(BarberShop $shop): Subscription
    {
        $plan = $this->defaultPlan();

        return $shop->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'trial',
            'starts_at' => Carbon::now(),
            'trial_ends_at' => Carbon::now()->addDays((int) $plan->trial_days),
        ]);
    }

    protected function defaultPlan(): Plan
    {
        return Plan::firstOrCreate(
            ['slug' => 'basico'],
            [
                'name' => 'Básico',
                'description' => 'Perfecto para barberías que están empezando. Hasta 3 barberos.',
                'max_barbers' => 3,
                'setup_price' => 20.00,
                'monthly_price' => 10.00,
                'trial_days' => 30,
                'is_active' => true,
            ]
        );
    }

    protected function uniqueSlug(string $shopName): string
    {
        $base = Str::slug($shopName) ?: 'barberia';

        do {
            $slug = $base . '-' . Str::lower(Str::random(4));
        } while (BarberShop::where('slug', $slug)->exists());

        return $slug;
    }
}
