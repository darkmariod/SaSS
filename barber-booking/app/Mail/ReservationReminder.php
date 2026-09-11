<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al cliente la tarde anterior a su cita. Reduce las ausencias, que es
 * el hueco de agenda que más le cuesta a una barbería.
 */
class ReservationReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: tu cita de mañana en ' . $this->reservation->barberShop->name,
        );
    }

    public function content(): Content
    {
        $r = $this->reservation;

        return new Content(
            markdown: 'emails.reservation-reminder',
            with: [
                'customer' => $r->customer_name,
                'shop' => $r->barberShop,
                'service' => $r->service->name,
                'barber' => $r->consultant?->name,
                'date' => $r->reservation_date->format('d/m/Y'),
                'time' => substr((string) $r->start_time, 0, 5),
            ],
        );
    }
}
