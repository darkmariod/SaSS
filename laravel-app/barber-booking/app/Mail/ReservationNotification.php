<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Reserva - ' . $this->reservation->service->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservation-notification',
            with: [
                'reservation' => $this->reservation,
                'shop' => $this->reservation->barberShop,
                'service' => $this->reservation->service,
                'customer' => $this->reservation->customer_name,
                'phone' => $this->reservation->customer_phone,
                'date' => $this->reservation->reservation_date->format('d/m/Y'),
                'time' => $this->reservation->start_time . ' - ' . $this->reservation->end_time,
                'total' => $this->reservation->total_amount,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
