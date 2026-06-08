<?php

namespace App\Services;

use App\Models\Reservation;

class ICalService
{
    public function generate(Reservation $reservation): string
    {
        $shop = $reservation->barberShop;
        $service = $reservation->service;
        $barber = $reservation->consultant;

        $dtStart = $reservation->reservation_date->format('Ymd') . 'T' . str_replace(':', '', $reservation->start_time);
        $dtEnd = $reservation->reservation_date->format('Ymd') . 'T' . str_replace(':', '', $reservation->end_time);
        $now = now()->format('Ymd\THis');
        $uid = $reservation->id . '-' . md5($reservation->created_at . $reservation->id) . '@barberbooking';

        $description = "Servicio: {$service?->name}\n";
        if ($reservation->addonService) {
            $description .= "Extra: {$reservation->addonService->name}\n";
        }
        $description .= "Barbero: {$barber?->name}\n";
        $description .= "Teléfono: {$reservation->customer_phone}\n";
        if ($reservation->customer_email) {
            $description .= "Email: {$reservation->customer_email}\n";
        }
        $description .= "Total: \$" . number_format((float) $reservation->total_amount, 2);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//BookingEc//ES',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTART:' . $dtStart,
            'DTEND:' . $dtEnd,
            'DTSTAMP:' . $now,
            'CREATED:' . $now,
            'LAST-MODIFIED:' . $now,
            'SUMMARY:Reserva - ' . ($service?->name ?? 'Corte') . ' - ' . $reservation->customer_name,
            'DESCRIPTION:' . $this->foldText($description),
            'LOCATION:' . ($shop?->address ?? 'Riobamba, Ecuador'),
            'STATUS:' . match ($reservation->reservation_status) {
                'confirmada' => 'CONFIRMED',
                'cancelada' => 'CANCELLED',
                default => 'TENTATIVE',
            },
            'BEGIN:VALARM',
            'TRIGGER:-PT1H',
            'ACTION:DISPLAY',
            'DESCRIPTION:Recordatorio de reserva',
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines);
    }

    private function foldText(string $text): string
    {
        $escaped = str_replace(["\r\n", "\n\r", "\n"], '\\n', $text);
        $escaped = str_replace(',', '\\,', $escaped);
        $escaped = str_replace(';', '\\;', $escaped);

        // RFC 5545 folding: max 75 octets per line
        if (mb_strlen($escaped) <= 75) {
            return $escaped;
        }

        $lines = [];
        $lines[] = mb_substr($escaped, 0, 75);

        $remaining = mb_substr($escaped, 75);
        while (mb_strlen($remaining) > 0) {
            $lines[] = ' ' . mb_substr($remaining, 0, 74);
            $remaining = mb_substr($remaining, 74);
        }

        return implode("\r\n", $lines);
    }
}
