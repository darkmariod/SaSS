<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Services\ICalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  'owner'|'barber'|'customer'  $audience  Who this copy is addressed to.
     */
    public function __construct(
        public Reservation $reservation,
        public string $audience = 'owner',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->audience === 'customer'
            ? 'Confirmación de reserva - ' . $this->reservation->service->name
            : 'Nueva reserva - ' . $this->reservation->service->name;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservation-notification',
            with: [
                'reservation' => $this->reservation,
                'audience' => $this->audience,
                'isStaff' => $this->audience !== 'customer',
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
        try {
            $ical = app(ICalService::class);
            $content = $ical->generate($this->reservation);

            return [
                \Illuminate\Mail\Attachment::fromData(
                    fn () => $content,
                    'reserva-' . $this->reservation->id . '.ics'
                )->withMime('text/calendar; charset=utf-8; method=PUBLISH'),
            ];
        } catch (\Exception $e) {
            report($e);
            return [];
        }
    }
}
