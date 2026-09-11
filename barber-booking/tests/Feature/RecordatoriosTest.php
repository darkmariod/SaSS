<?php

namespace Tests\Feature;

use App\Mail\ReservationReminder;
use App\Mail\WhatsAppReminderList;
use App\Models\Reservation;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * El recordatorio de la tarde anterior es lo que baja las ausencias. Tiene que
 * salir una sola vez, solo para mañana, y por el canal que el cliente dejó.
 */
class RecordatoriosTest extends TestCase
{
    use RefreshDatabase;

    private $barberia;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $dueño = User::factory()->create(['is_active' => true, 'email' => 'dueno@ejemplo.com']);
        $this->barberia = app(TenantProvisioningService::class)->provision($dueño, 'Barbería de Prueba');
    }

    private function cita(string $fecha, ?string $email = 'cliente@ejemplo.com', string $estado = 'confirmada'): Reservation
    {
        return Reservation::create([
            'barber_shop_id' => $this->barberia->id,
            'service_id' => $this->barberia->services()->where('service_type', 'option')->first()->id,
            'consultant_id' => $this->barberia->owner_id,
            'customer_name' => 'Cliente Prueba',
            'customer_phone' => '0991234567',
            'customer_email' => $email,
            'reservation_date' => $fecha,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'reservation_status' => $estado,
            'total_amount' => 5,
            'payment_status' => 'pendiente',
        ]);
    }

    public function test_avisa_por_correo_la_cita_de_mañana(): void
    {
        $cita = $this->cita(today()->addDay()->toDateString());

        $this->artisan('reminders:send')->assertSuccessful();

        Mail::assertQueued(ReservationReminder::class, fn ($m) => $m->hasTo('cliente@ejemplo.com'));
        $this->assertNotNull($cita->fresh()->reminder_sent_at, 'La cita debe quedar marcada como avisada.');
    }

    public function test_no_avisa_dos_veces(): void
    {
        $this->cita(today()->addDay()->toDateString());

        $this->artisan('reminders:send');
        $this->artisan('reminders:send');

        Mail::assertQueued(ReservationReminder::class, 1);
    }

    public function test_no_avisa_citas_de_hoy_ni_de_pasado_mañana(): void
    {
        $this->cita(today()->toDateString());
        $this->cita(today()->addDays(2)->toDateString());

        $this->artisan('reminders:send');

        Mail::assertNothingOutgoing();
    }

    public function test_no_avisa_una_cita_cancelada(): void
    {
        $this->cita(today()->addDay()->toDateString(), estado: 'cancelada');

        $this->artisan('reminders:send');

        Mail::assertNothingOutgoing();
    }

    public function test_sin_correo_el_dueño_recibe_el_enlace_de_whatsapp(): void
    {
        // El cliente que solo dejó teléfono no puede recibir un correo. El
        // dueño recibe un enlace listo para tocar y enviar por WhatsApp.
        $this->cita(today()->addDay()->toDateString(), email: null);

        $this->artisan('reminders:send');

        Mail::assertNotQueued(ReservationReminder::class);
        Mail::assertQueued(WhatsAppReminderList::class, fn ($m) => $m->hasTo('dueno@ejemplo.com'));
    }

    public function test_el_enlace_de_whatsapp_lleva_el_numero_en_formato_internacional(): void
    {
        $this->cita(today()->addDay()->toDateString(), email: null);

        $this->artisan('reminders:send');

        Mail::assertQueued(WhatsAppReminderList::class, function (WhatsAppReminderList $m) {
            $html = $m->render();

            return str_contains($html, 'wa.me/593991234567');
        });
    }

    public function test_la_simulacion_no_envia_ni_marca_nada(): void
    {
        $cita = $this->cita(today()->addDay()->toDateString());

        $this->artisan('reminders:send --dry-run');

        Mail::assertNothingOutgoing();
        $this->assertNull($cita->fresh()->reminder_sent_at);
    }

    public function test_el_recordatorio_esta_programado_cada_tarde(): void
    {
        $eventos = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        $this->assertTrue(
            $eventos->contains(fn ($e) => str_contains($e->command ?? '', 'reminders:send')),
            'reminders:send debe estar en el programador.',
        );
    }
}
