<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\CashRegister;
use App\Models\ReservationDetail;
use App\Models\Transfer;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se especificó end_time, usar end_time = start_time + 1 hora por defecto
        if (empty($data['end_time'])) {
            $start = \Carbon\Carbon::parse($data['start_time'] ?? '09:00');
            $data['end_time'] = $start->copy()->addHour()->format('H:i:s');
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $reservation = $this->record;

        ReservationDetail::create([
            'reservation_id' => $reservation->id,
            'payment_method' => 'bank_transfer',
            'payment_status' => $reservation->payment_status ?? 'pendiente',
            'amount' => $reservation->total_amount,
            'paid_at' => $reservation->payment_status === 'pagado' ? now() : null,
        ]);

        if ($reservation->payment_status === 'pagado') {
            $transfer = Transfer::create([
                'barber_shop_id' => $reservation->barber_shop_id,
                'reservation_id' => $reservation->id,
                'reference' => 'ADMIN-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'amount' => $reservation->total_amount,
                'status' => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            $reservation->update(['transfer_id' => $transfer->id]);
        }

        CashRegister::query()
            ->where('barber_shop_id', $reservation->barber_shop_id)
            ->whereDate('date', $reservation->reservation_date)
            ->first()
            ?->recalculate();
    }
}
