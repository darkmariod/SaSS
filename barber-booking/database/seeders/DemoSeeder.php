<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\Service;
use App\Models\Transfer;
use App\Models\User;
use App\Models\BarberShop;
use App\Models\BarberProfile;
use App\Models\BarberAvailability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Limpiar datos demo previos ───
        User::where('email', 'jean@test.com')->delete();
        // Eliminar shops demo con slug viejo para evitar duplicados
        BarberShop::where('slug', 'barberia-seven')->delete();

        // ─── Owner ───
        $owner = User::updateOrCreate(
            ['email' => 'owner@test.com'],
            [
                'name' => 'Dueño Demo',
                'phone' => '0999999999',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $owner->syncRoles('owner');

        // ─── Admin ───
        $admin = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Demo',
                'phone' => '0999999998',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles('admin');

        // ─── Cliente (sin acceso a panel, solo para referencia) ───
        User::firstOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'name' => 'Cliente Demo',
                'phone' => '0977777777',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // ─── Barbería ───
        $shop = BarberShop::updateOrCreate(
            ['slug' => 'barberia-demo'],
            [
                'owner_id' => $owner->id,
                'name' => 'Barbería Demo',
                'phone' => '0999999999',
                'address' => 'Centro de Riobamba',
                'city' => 'Riobamba',
                'description' => 'Reserva tu corte online con nuestros barberos.',
                'bank_name' => 'Banco Pichincha',
                'bank_account' => '0000000000',
                'bank_account_owner' => 'Barbería Demo',
                'is_active' => true,
            ]
        );

        // ─── Servicios ───
        $mensCut = Service::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'name' => 'Mens Cut'],
            [
                'category' => 'Men',
                'description' => 'Servicio principal de corte masculino.',
                'service_type' => 'main',
                'parent_service_id' => null,
                'duration_minutes' => 15,
                'price' => 0.00,
                'requires_payment' => false,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $fade = Service::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'name' => 'Fade'],
            [
                'category' => 'Men', 'description' => 'Corte fade profesional.',
                'service_type' => 'option', 'parent_service_id' => $mensCut->id,
                'duration_minutes' => 45, 'price' => 5.00,
                'requires_payment' => true, 'is_active' => true, 'sort_order' => 1,
            ]
        );

        $fadeBeard = Service::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'name' => 'Fade & Beard'],
            [
                'category' => 'Men', 'description' => 'Corte fade más arreglo de barba.',
                'service_type' => 'option', 'parent_service_id' => $mensCut->id,
                'duration_minutes' => 70, 'price' => 8.00,
                'requires_payment' => true, 'is_active' => true, 'sort_order' => 2,
            ]
        );

        $scissorCut = Service::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'name' => 'Scissor Cut'],
            [
                'category' => 'Men', 'description' => 'Corte con tijera y acabado clásico.',
                'service_type' => 'option', 'parent_service_id' => $mensCut->id,
                'duration_minutes' => 40, 'price' => 6.00,
                'requires_payment' => true, 'is_active' => true, 'sort_order' => 3,
            ]
        );

        $beardOnly = Service::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'name' => 'Beard Only'],
            [
                'category' => 'Men', 'description' => 'Arreglo y perfilado de barba.',
                'service_type' => 'option', 'parent_service_id' => $mensCut->id,
                'duration_minutes' => 30, 'price' => 4.00,
                'requires_payment' => true, 'is_active' => true, 'sort_order' => 4,
            ]
        );

        $cejas = Service::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'name' => 'Cejas'],
            [
                'category' => 'Extras', 'description' => 'Limpieza y perfilado de cejas.',
                'service_type' => 'addon',
                'duration_minutes' => 15, 'price' => 2.00,
                'requires_payment' => false, 'is_active' => true, 'sort_order' => 1,
            ]
        );

        // ─── Limpiar perfiles de barbero huérfanos ───
        BarberProfile::where('barber_shop_id', '!=', $shop->id)
            ->whereHas('barberShop', fn($q) => $q->where('slug', 'barberia-seven'))
            ->delete();

        // ─── Limpiar barberos huérfanos de pruebas anteriores ───
        User::where('email', 'barber@hotmail.com')->delete();

        // ─── Barberos ───
        $barber1 = User::updateOrCreate(
            ['email' => 'barber@test.com'],
            [
                'name' => 'barbero-1',
                'phone' => '0988888881',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $barber1->syncRoles('barber'); // Usamos syncRoles para asegurar que SOLO tenga role barber

        $barber2 = User::updateOrCreate(
            ['email' => 'barbero@prueba.com'],
            [
                'name' => 'barbero-2',
                'phone' => '0988888882',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $barber2->syncRoles('barber');

        // ─── Barbero-4: para la demo con credenciales propias ───
        $barber4 = User::updateOrCreate(
            ['email' => 'barber@hotmail.com'],
            [
                'name' => 'barbero-4',
                'phone' => '0988888884',
                'password' => Hash::make('barber593'),
                'is_active' => true,
            ]
        );
        $barber4->syncRoles('barber');

        // ─── Barber Profiles ───
        $profile1 = BarberProfile::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'user_id' => $barber1->id],
            [
                'display_name' => 'barbero-1',
                'bio' => 'Especialista en cortes clásicos y degradados.',
                'commission_percentage' => 50.00,
                'is_active' => true,
            ]
        );

        $profile2 = BarberProfile::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'user_id' => $barber2->id],
            [
                'display_name' => 'barbero-2',
                'bio' => 'Experto en barba y estilos modernos.',
                'commission_percentage' => 50.00,
                'is_active' => true,
            ]
        );

        $profile3 = BarberProfile::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'user_id' => $barber4->id],
            [
                'display_name' => 'barbero-4',
                'bio' => 'Barbero versátil para todo tipo de estilos.',
                'commission_percentage' => 40.00,
                'is_active' => true,
            ]
        );

        // ─── Barber Availabilities ───
        foreach ([$profile1, $profile2, $profile3] as $profile) {
            foreach ([1, 2, 3, 4, 5, 6] as $day) {
                BarberAvailability::updateOrCreate(
                    ['barber_profile_id' => $profile->id, 'day_of_week' => $day],
                    ['start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_active' => true]
                );
            }
            // Domingo medio día
            BarberAvailability::updateOrCreate(
                ['barber_profile_id' => $profile->id, 'day_of_week' => 0],
                ['start_time' => '09:00:00', 'end_time' => '14:00:00', 'is_active' => true]
            );
        }

        // ─── Reservas Demo ───
        $services = [$fade->id, $fadeBeard->id, $scissorCut->id, $beardOnly->id];
        $addons = [null, $cejas->id];
        $customerNames = [
            'Carlos López', 'María García', 'Pedro Sánchez', 'Ana Martínez',
            'Luis Rodríguez', 'Sofía Torres', 'Diego Ramírez', 'Valentina Herrera',
        ];
        $statuses = ['pendiente', 'confirmada', 'completada'];

        // Limpiar reservas demo existentes (en transacción para FK)
        DB::transaction(function () use ($shop) {
            $ids = Reservation::where('barber_shop_id', $shop->id)->pluck('id');
            ReservationDetail::whereIn('reservation_id', $ids)->delete();
            Transfer::where('barber_shop_id', $shop->id)->delete();
            Reservation::where('barber_shop_id', $shop->id)->delete();
        });

        $today = now()->startOfDay();

        // Crear reservas para los últimos 2 días, hoy, y próximos 3 días
        foreach ([-2, -1, 0, 1, 2, 3] as $dayOffset) {
            $date = (clone $today)->addDays($dayOffset);

            // Saltar si es pasado lejano (más de 3 días atrás ya se cubre con -2,-1)
            // Cada día creamos 2-4 reservas distribuidas entre los 2 barberos
            $profiles = [$profile1, $profile2, $profile3];
            $reservationsForDay = rand(3, 6);

            for ($i = 0; $i < $reservationsForDay; $i++) {
                $profile = $profiles[$i % 3];
                $serviceIdx = array_rand($services);
                $serviceId = $services[$serviceIdx];
                $addonId = $addons[array_rand($addons)];
                $customerName = $customerNames[array_rand($customerNames)];
                $phone = '09' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

                // Horas escalonadas: 09:00, 10:00, 11:00, 14:00, 15:00, 16:00
                $baseHour = [9, 10, 11, 14, 15, 16][$i % 6];
                $startTime = sprintf('%02d:00:00', $baseHour);

                // Calcular end_time según servicio
                $svc = Service::find($serviceId);
                $duration = (int) $svc->duration_minutes;
                if ($addonId) {
                    $addonSvc = Service::find($addonId);
                    $duration += (int) $addonSvc->duration_minutes;
                }
                $startCarbon = \Carbon\Carbon::parse($date->toDateString() . ' ' . $startTime);
                $endCarbon = (clone $startCarbon)->addMinutes($duration);
                $endTime = $endCarbon->format('H:i:s');

                $totalAmount = (float) $svc->price;
                if ($addonId) {
                    $totalAmount += (float) Service::find($addonId)->price;
                }

                // Determinar estado según la fecha
                if ($date->isPast()) {
                    $resStatus = 'completada';
                    $payStatus = 'pagado';
                } elseif ($date->isToday()) {
                    // Hoy: mezcla de pendiente, confirmada, completada
                    $resStatus = $statuses[$i % 3];
                    $payStatus = match ($resStatus) {
                        'completada' => 'pagado',
                        'confirmada' => 'pagado',
                        default => 'pendiente',
                    };
                } else {
                    // Futuro: pendiente o confirmada
                    $resStatus = ($i % 3 === 0) ? 'confirmada' : 'pendiente';
                    $payStatus = ($resStatus === 'confirmada') ? 'pagado' : 'pendiente';
                }

                $reservation = Reservation::create([
                    'barber_shop_id' => $shop->id,
                    'service_id' => $serviceId,
                    'addon_service_id' => $addonId,
                    'user_id' => null,
                    'consultant_id' => $profile->user_id,
                    'customer_name' => $customerName,
                    'customer_phone' => $phone,
                    'reservation_date' => $date->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'reservation_status' => $resStatus,
                    'total_amount' => $totalAmount,
                    'payment_status' => $payStatus,
                ]);

                // ReservationDetail
                ReservationDetail::create([
                    'reservation_id' => $reservation->id,
                    'payment_method' => 'bank_transfer',
                    'payment_status' => $payStatus,
                    'amount' => $totalAmount,
                    'paid_at' => in_array($payStatus, ['pagado']) ? now()->subHours(rand(1, 24)) : null,
                ]);

                // Si está pagada y es hoy/pasado, crear transferencia confirmada
                if ($payStatus === 'pagado') {
                    $transfer = Transfer::create([
                        'barber_shop_id' => $shop->id,
                        'reservation_id' => $reservation->id,
                        'reference' => 'REF-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                        'amount' => $totalAmount,
                        'status' => 'confirmed',
                        'confirmed_by' => $owner->id,
                        'confirmed_at' => $date->isPast()
                            ? (clone $date)->addHours(rand(9, 17))->format('Y-m-d H:i:s')
                            : now()->subHours(rand(1, 5)),
                    ]);

                    $reservation->update(['transfer_id' => $transfer->id]);
                }
            }
        }

        // ─── Una reserva pendiente con comprobante subido (para el widget de comprobantes) ───
        $pendingReceiptReservation = Reservation::create([
            'barber_shop_id' => $shop->id,
            'service_id' => $fade->id,
            'addon_service_id' => $cejas->id,
            'user_id' => null,
            'consultant_id' => $barber1->id,
            'customer_name' => 'Juan Pérez (pendiente pago)',
            'customer_phone' => '0991112233',
            'reservation_date' => $today->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '18:00:00',
            'reservation_status' => 'pendiente',
            'total_amount' => 7.00,
            'payment_status' => 'comprobante_subido',
        ]);

        ReservationDetail::create([
            'reservation_id' => $pendingReceiptReservation->id,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'comprobante_subido',
            'amount' => 7.00,
        ]);

        Transfer::create([
            'barber_shop_id' => $shop->id,
            'reservation_id' => $pendingReceiptReservation->id,
            'reference' => 'REF-PEND-001',
            'amount' => 7.00,
            'status' => 'pending',
            'receipt_image_path' => null,
            'notes' => 'Comprobante pendiente de revisión (demo).',
        ]);

        // ─── Caja abierta para hoy (para que el widget muestre datos reales) ───
        CashRegister::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'date' => $today->toDateString()],
            [
                'opening_amount' => 50.00,
                'status' => 'open',
                'opened_by' => $owner->id,
                'opened_at' => now()->startOfDay()->addHours(8),
            ]
        );
        // Recalcular caja para que los montos del sistema se reflejen
        $cashRegister = CashRegister::where('barber_shop_id', $shop->id)
            ->where('date', $today->toDateString())
            ->first();
        if ($cashRegister) {
            $cashRegister->recalculate();
        }
    }
}
