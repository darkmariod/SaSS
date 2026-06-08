<?php

namespace App\Services;

use App\Models\Reservation;
use Google\Client as GoogleClient;
use Google\Service\Calendar as CalendarService;
use Google\Service\Calendar\Event as GoogleEvent;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventReminders;
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

    private ?GoogleClient $client = null;

    public function __construct()
    {
        $this->enabled = config('services.google.calendar_enabled', false);
        $this->credentialsPath = config('services.google.calendar_credentials');
    }

    /**
     * Obtener servicio de Google Calendar autenticado.
     */
    private function getService(): ?CalendarService
    {
        if (! $this->enabled || ! $this->credentialsPath) {
            return null;
        }

        if ($this->client === null) {
            $fullPath = storage_path($this->credentialsPath);

            if (! file_exists($fullPath)) {
                Log::warning('Google Calendar: credentials file not found at ' . $fullPath);

                return null;
            }

            $this->client = new GoogleClient;
            $this->client->setAuthConfig($fullPath);
            $this->client->addScope(CalendarService::CALENDAR);
        }

        return new CalendarService($this->client);
    }

    /**
     * Obtener el calendar ID a usar (por defecto 'primary').
     */
    private function getCalendarId(): string
    {
        return config('services.google.calendar_id', 'primary');
    }

    /**
     * Construir el summary del evento.
     */
    private function buildEventSummary(Reservation $reservation): string
    {
        $serviceName = $reservation->service?->name ?? 'Servicio';

        return "{$serviceName} - {$reservation->customer_name}";
    }

    /**
     * Construir la descripción del evento con todos los detalles.
     */
    private function buildEventDescription(Reservation $reservation): string
    {
        $lines = [
            "Cliente: {$reservation->customer_name}",
            "Teléfono: {$reservation->customer_phone}",
        ];

        if ($reservation->customer_email) {
            $lines[] = "Email: {$reservation->customer_email}";
        }

        $lines[] = 'Barbero: ' . ($reservation->consultant?->name ?? 'N/A');
        $lines[] = 'Barbería: ' . ($reservation->barberShop?->name ?? 'N/A');
        $lines[] = "Estado: {$reservation->reservation_status}";
        $lines[] = 'Total: $' . number_format((float) $reservation->total_amount, 2);

        if ($reservation->addonService) {
            $lines[] = "Extra: {$reservation->addonService->name}";
        }

        return implode("\n", $lines);
    }

    /**
     * Crear EventDateTime a partir de una reserva.
     */
    private function buildEventDateTime(Reservation $reservation, string $timeField): EventDateTime
    {
        $time = $reservation->{$timeField};

        // El time ya viene con segundos (H:i:s), no agregar :00 extra
        if (str_contains($time, ':')) {
            $parts = explode(':', $time);
            if (count($parts) === 2) {
                $time .= ':00';
            }
        }

        $datetime = new EventDateTime;
        $datetime->setDateTime(
            $reservation->reservation_date->format('Y-m-d') . 'T' . $time
        );
        $datetime->setTimeZone(config('app.timezone', 'America/Argentina/Buenos_Aires'));

        return $datetime;
    }

    /**
     * Configurar los reminders del evento (1 hora antes).
     */
    private function buildReminders(): EventReminders
    {
        $reminders = new EventReminders;
        $reminders->setUseDefault(false);
        $reminders->setOverrides([
            new \Google\Service\Calendar\EventReminder([
                'method' => 'popup',
                'minutes' => 60,
            ]),
        ]);

        return $reminders;
    }

    /**
     * Crear evento en Google Calendar para una reserva.
     * Retorna el event ID o null si falla.
     */
    public function createEvent(Reservation $reservation): ?string
    {
        $service = $this->getService();
        if (! $service) {
            Log::info('Google Calendar: disabled or not configured, skipping event creation');

            return null;
        }

        try {
            $event = new GoogleEvent;
            $event->setSummary($this->buildEventSummary($reservation));
            $event->setDescription($this->buildEventDescription($reservation));
            $event->setStart($this->buildEventDateTime($reservation, 'start_time'));
            $event->setEnd($this->buildEventDateTime($reservation, 'end_time'));
            $event->setReminders($this->buildReminders());

            $calendarId = $this->getCalendarId();
            $createdEvent = $service->events->insert($calendarId, $event);

            Log::info('Google Calendar: event created', [
                'reservation_id' => $reservation->id,
                'event_id' => $createdEvent->id,
            ]);

            return $createdEvent->id;
        } catch (\Exception $e) {
            Log::error('Google Calendar: error creating event', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Actualizar evento en Google Calendar.
     * Si el evento fue eliminado externamente, lo recrea.
     */
    public function updateEvent(Reservation $reservation): ?string
    {
        // Si no tiene event ID, crear uno nuevo
        if (! $reservation->google_event_id) {
            return $this->createEvent($reservation);
        }

        $service = $this->getService();
        if (! $service) {
            return $reservation->google_event_id;
        }

        try {
            $calendarId = $this->getCalendarId();

            // Obtener evento existente
            $event = $service->events->get($calendarId, $reservation->google_event_id);

            // Actualizar campos
            $event->setSummary($this->buildEventSummary($reservation));
            $event->setDescription($this->buildEventDescription($reservation));
            $event->setStart($this->buildEventDateTime($reservation, 'start_time'));
            $event->setEnd($this->buildEventDateTime($reservation, 'end_time'));
            $event->setReminders($this->buildReminders());

            $updatedEvent = $service->events->update($calendarId, $reservation->google_event_id, $event);

            Log::info('Google Calendar: event updated', [
                'reservation_id' => $reservation->id,
                'event_id' => $updatedEvent->id,
            ]);

            return $updatedEvent->id;
        } catch (\Exception $e) {
            Log::error('Google Calendar: error updating event', [
                'reservation_id' => $reservation->id,
                'event_id' => $reservation->google_event_id,
                'error' => $e->getMessage(),
            ]);

            // Si el evento fue eliminado externamente, recrearlo
            if (str_contains($e->getMessage(), 'not found')) {
                return $this->createEvent($reservation);
            }

            return $reservation->google_event_id;
        }
    }

    /**
     * Eliminar evento en Google Calendar (para cancelaciones).
     */
    public function deleteEvent(Reservation $reservation): bool
    {
        if (! $this->enabled || ! $reservation->google_event_id) {
            return false;
        }

        $service = $this->getService();
        if (! $service) {
            return false;
        }

        try {
            $calendarId = $this->getCalendarId();
            $service->events->delete($calendarId, $reservation->google_event_id);

            Log::info('Google Calendar: event deleted', [
                'reservation_id' => $reservation->id,
                'event_id' => $reservation->google_event_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar: error deleting event', [
                'reservation_id' => $reservation->id,
                'event_id' => $reservation->google_event_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
