<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $paypalOrder;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($paypalOrder, $user)
    {
        $this->paypalOrder = $paypalOrder;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('✅ Confirmación de Reserva')
                    ->view('emails.reservation_confirmed')
                    ->with([
                        'paypalOrder' => $this->paypalOrder,
                        'user' => $this->user,
                    ]);
    }

}
