<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\CashRegister;
use App\Models\Transfer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $reservation = $this->record;
        $detail = $reservation->detail;

        if ($detail) {
            $detail->update([
                'amount' => $reservation->total_amount,
                'payment_status' => $reservation->payment_status,
                'paid_at' => $reservation->payment_status === 'pagado'
                    ? ($detail->paid_at ?? now())
                    : null,
            ]);
        }

        if ($reservation->payment_status === 'pagado' && ! $reservation->transfer_id) {
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
