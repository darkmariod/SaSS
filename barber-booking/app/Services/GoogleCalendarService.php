<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

/**
 * Google Calendar Sync Service
 *
 * Para activar:
 * 1. Crear proyecto en https://console.cloud.google.com
 * 2. Habilitar Google Calendar API
 * 3. Crear Service Account y descargar JSON de credenciales
 * 4. Guardar JSON en storage/app/google-calendar/credentials.json
 * 5. Compartir cada calendario de barbería con el email de la service account
 * 6. Agregar al .env:
 *    GOOGLE_CALENDAR_ENABLED=true
 *    GOOGLE_CALENDAR_CREDENTIALS=storage/app/google-calendar/credentials.json
 */
class GoogleCalendarService
{
    private bool $enabled;

    private ?string $credentialsPath;

    public function __construct()
    {
        $this->enabled = config('services.google.calendar_enabled', false);
        $this->credentialsPath = config('services.google.calendar_credentials');
    }

    /**
     * Crear evento en Google Calendar para una reserva.
     * Retorna el event ID o null si falla.
     */
    public function createEvent(Reservation $reservation): ?string
    {
        if (! $this->enabled || ! $this->credentialsPath) {
            Log::info('Google Calendar: disabled, skipping event creation');
            return null;
        }

        // TODO: Implementar con google/apiclient cuando se configuren credenciales
        // 
        // Pasos:
        // 1. $client = new \Google\Client();
        // 2. $client->setAuthConfig(storage_path($this->credentialsPath));
        // 3. $client->addScope(\Google\Service\Calendar::CALENDAR);
        // 4. $service = new \Google\Service\Calendar($client);
        // 5. Crear evento con:
        //    - Summary: "Reserva - {service} - {customer}"
        //    - Description: detalles completos
        //    - Start/End: fechas y horas
        //    - Attendees: [owner email, customer email]
        //    - Reminders: 1 hora antes
        // 6. $calendarId = config('services.google.calendar_id');
        //    $event = $service->events->insert($calendarId, $event);
        // 7. Guardar event->id en $reservation->google_event_id

        Log::info('Google Calendar: credentials not configured, cannot create event', [
            'reservation_id' => $reservation->id,
        ]);

        return null;
    }

    /**
     * Actualizar evento en Google Calendar.
     */
    public function updateEvent(Reservation $reservation): ?string
    {
        if (! $this->enabled || ! $reservation->google_event_id) {
            return null;
        }

        // TODO: Implementar actualización con google/apiclient
        // $service->events->update($calendarId, $reservation->google_event_id, $event);

        return $reservation->google_event_id;
    }

    /**
     * Eliminar evento en Google Calendar (para cancelaciones).
     */
    public function deleteEvent(Reservation $reservation): bool
    {
        if (! $this->enabled || ! $reservation->google_event_id) {
            return false;
        }

        // TODO: Implementar eliminación con google/apiclient
        // $service->events->delete($calendarId, $reservation->google_event_id);

        return false;
    }
}
