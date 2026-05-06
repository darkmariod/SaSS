<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification;

class ReservationWhatsApp extends Notification
{
    protected Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via($notifiable): array
    {
        return ['mail']; // Lo usamos como carrier, pero el enlace es para WhatsApp
    }

    public function toMail($notifiable): MailMessage
    {
        $shop = $this->reservation->barberShop;
        $customer = $this->reservation->customer_name;
        $phone = $this->reservation->customer_phone;
        $service = $this->reservation->service->name;
        $date = $this->reservation->reservation_date->format('d/m/Y');
        $time = $this->reservation->start_time . ' - ' . $this->reservation->end_time;
        $total = number_format($this->reservation->total_amount, 2);

        // Usar \n real, urlencode() convertirá a %0A automáticamente
        $message = "💈 *Nueva Reserva* 💈\n";
        $message .= "*Barbería:* {$shop->name}\n";
        $message .= "*Cliente:* {$customer}\n";
        $message .= "*Teléfono:* {$phone}\n";
        $message .= "*Servicio:* {$service}\n";
        $message .= "*Fecha:* {$date}\n";
        $message .= "*Hora:* {$time}\n";
        $message .= "*Total:* $$total\n";
        $message .= "\n_Revisa en el panel:_ " . url('/admin/reservations/' . $this->reservation->id);

        // Generar enlace wa.me (WhatsApp abre directo)
        $shopPhone = str_replace(['+', ' ', '-', '(', ')'], '', $shop->phone ?? '593999999999');
        $waLink = "https://wa.me/{$shopPhone}?text=" . urlencode($message);

        return (new MailMessage)
            ->subject('Nueva Reserva - ' . $service)
            ->line('Se ha realizado una nueva reserva:')
            ->line('Cliente: ' . $customer)
            ->line('Teléfono: ' . $phone)
            ->line('Servicio: ' . $service)
            ->line('Fecha: ' . $date . ' ' . $time)
            ->line('Total: $' . $total)
            ->action('Abrir WhatsApp', $waLink);
    }
}
