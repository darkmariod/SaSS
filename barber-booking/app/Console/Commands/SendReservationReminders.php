<?php

namespace App\Console\Commands;

use App\Mail\ReservationReminder;
use App\Models\BarberShop;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Manda los recordatorios de las citas de mañana.
 *
 * Al cliente con correo le llega un mensaje. Para el cliente que solo dejó
 * teléfono no hay envío automático posible (WhatsApp exige la API de Meta o un
 * proveedor como Twilio, con credenciales que esta instalación no tiene), así
 * que el dueño recibe un correo con un enlace wa.me por cada cita: toca y el
 * mensaje ya sale escrito.
 *
 * Es idempotente: cada cita se marca al avisar, así una segunda corrida el
 * mismo día no repite nada.
 */
class SendReservationReminders extends Command
{
    protected $signature = 'reminders:send
                            {--date= : Fecha de las citas a recordar (por defecto, mañana)}
                            {--dry-run : Mostrar qué se enviaría sin enviar nada}';

    protected $description = 'Envía los recordatorios de las citas del día siguiente';

    public function handle(): int
    {
        $fecha = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))->toDateString()
            : today()->addDay()->toDateString();

        $simulacion = (bool) $this->option('dry-run');

        $citas = Reservation::query()
            ->with(['barberShop', 'service', 'consultant'])
            ->whereDate('reservation_date', $fecha)
            ->whereIn('reservation_status', ['pendiente', 'confirmada'])
            ->whereNull('reminder_sent_at')
            ->orderBy('barber_shop_id')
            ->orderBy('start_time')
            ->get();

        $this->line("  Citas para {$fecha} sin recordatorio: {$citas->count()}");

        if ($citas->isEmpty()) {
            return self::SUCCESS;
        }

        $porCorreo = 0;
        $porWhatsapp = [];

        foreach ($citas as $cita) {
            if ($cita->customer_email) {
                $simulacion || Mail::to($cita->customer_email)->send(new ReservationReminder($cita));
                $porCorreo++;
                $this->line("    correo    → {$cita->customer_name} ({$cita->customer_email})");
            } elseif ($cita->customer_phone) {
                $porWhatsapp[$cita->barber_shop_id][] = $cita;
                $this->line("    whatsapp  → {$cita->customer_name} ({$cita->customer_phone})");
            }

            $simulacion || $cita->forceFill(['reminder_sent_at' => now()])->saveQuietly();
        }

        // Al dueño: un correo por barbería con los enlaces para tocar y enviar.
        foreach ($porWhatsapp as $shopId => $lista) {
            $shop = BarberShop::find($shopId);

            if ($shop?->owner?->email && ! $simulacion) {
                Mail::to($shop->owner->email)->send(new \App\Mail\WhatsAppReminderList($shop, $lista, $fecha));
            }

            $this->line("    lista de " . count($lista) . " enlace(s) WhatsApp → dueño de {$shop?->name}");
        }

        $this->newLine();
        $this->info($simulacion
            ? "  Simulación: {$porCorreo} correo(s) y " . array_sum(array_map('count', $porWhatsapp)) . " enlace(s) WhatsApp. Nada enviado."
            : "  Enviados: {$porCorreo} correo(s) a clientes, " . count($porWhatsapp) . " lista(s) al dueño.");

        return self::SUCCESS;
    }
}
