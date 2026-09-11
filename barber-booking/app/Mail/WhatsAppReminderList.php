<?php

namespace App\Mail;

use App\Models\BarberShop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Lista de recordatorios por WhatsApp para el dueño: un enlace wa.me por cita,
 * con el mensaje ya escrito. Sustituye el envío automático, que requiere una
 * API de pago.
 */
class WhatsAppReminderList extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param  array<int, \App\Models\Reservation>  $citas */
    public function __construct(
        public BarberShop $shop,
        public array $citas,
        public string $fecha,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recordatorios por WhatsApp para mañana · ' . count($this->citas) . ' cita(s)');
    }

    public function content(): Content
    {
        $enlaces = array_map(function ($cita) {
            $tel = preg_replace('/\D+/', '', (string) $cita->customer_phone);
            // Número local ecuatoriano (09...) → internacional (5939...).
            if (str_starts_with($tel, '0')) {
                $tel = '593' . substr($tel, 1);
            }

            $hora = substr((string) $cita->start_time, 0, 5);
            $texto = "Hola {$cita->customer_name}, te recordamos tu cita de mañana a las {$hora} en {$this->shop->name} ({$cita->service->name}). ¡Te esperamos!";

            return [
                'cliente' => $cita->customer_name,
                'hora' => $hora,
                'servicio' => $cita->service->name,
                'url' => 'https://wa.me/' . $tel . '?text=' . rawurlencode($texto),
            ];
        }, $this->citas);

        return new Content(
            markdown: 'emails.whatsapp-reminder-list',
            with: ['shop' => $this->shop, 'fecha' => $this->fecha, 'enlaces' => $enlaces],
        );
    }
}
