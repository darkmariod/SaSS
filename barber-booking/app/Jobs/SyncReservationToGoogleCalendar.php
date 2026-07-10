<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Off-loads Google Calendar HTTP calls out of the web request so booking,
 * status changes and transfer confirmations never block on a remote API
 * (and never hold a DB transaction open while waiting on the network).
 */
class SyncReservationToGoogleCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  'sync'|'delete'  $action
     */
    public function __construct(
        public string $action,
        public ?int $reservationId = null,
        public ?string $googleEventId = null,
    ) {}

    public function handle(GoogleCalendarService $calendar): void
    {
        if ($this->action === 'delete') {
            if ($this->googleEventId) {
                $calendar->deleteEventById($this->googleEventId);
            }

            return;
        }

        $reservation = Reservation::find($this->reservationId);
        if (! $reservation) {
            return;
        }

        $eventId = $reservation->google_event_id
            ? $calendar->updateEvent($reservation)
            : $calendar->createEvent($reservation);

        if ($eventId && $eventId !== $reservation->google_event_id) {
            $reservation->updateQuietly(['google_event_id' => $eventId]);
        }
    }
}
