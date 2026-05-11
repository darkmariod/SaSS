<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewReceipt')
                ->label('Ver comprobante')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->url(function () {
                    $receiptImage = $this->record->detail?->receipt_image;

                    if (! $receiptImage) {
                        return null;
                    }

                    return asset('storage/' . $receiptImage);
                })
                ->openUrlInNewTab()
                ->visible(function (): bool {
                    return filled($this->record->detail?->receipt_image);
                }),

            Action::make('approvePayment')
                ->label('Aprobar pago')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprobar comprobante')
                ->modalDescription('Esto marcará el pago como pagado y confirmará la reserva.')
                ->modalSubmitActionLabel('Sí, aprobar pago')
                ->visible(function (): bool {
                    return auth()->user()?->hasAnyRole(['owner', 'admin'])
                        && $this->record->payment_status === 'comprobante_subido';
                })
                ->action(function (): void {
                    $this->record->update([
                        'payment_status' => 'pagado',
                        'reservation_status' => 'confirmada',
                    ]);

                    $this->record->detail?->update([
                        'payment_status' => 'pagado',
                        'paid_at' => now(),
                    ]);

                    $this->record->refresh();
                }),

            Action::make('rejectPayment')
                ->label('Rechazar pago')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechazar comprobante')
                ->modalDescription('Esto marcará el pago como rechazado y cancelará la reserva. El horario volverá a quedar disponible.')
                ->modalSubmitActionLabel('Sí, rechazar pago')
                ->visible(function (): bool {
                    return auth()->user()?->hasAnyRole(['owner', 'admin'])
                        && $this->record->payment_status === 'comprobante_subido';
                })
                ->action(function (): void {
                    $this->record->update([
                        'payment_status' => 'rechazado',
                        'reservation_status' => 'cancelada',
                    ]);

                    $this->record->detail?->update([
                        'payment_status' => 'rechazado',
                    ]);

                    $this->record->refresh();
                }),

            Action::make('confirmReservation')
                ->label('Confirmar cita')
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmar cita')
                ->modalDescription('Esto cambiará la reserva a confirmada.')
                ->modalSubmitActionLabel('Sí, confirmar cita')
                ->visible(function (): bool {
                    return auth()->user()?->hasAnyRole(['owner', 'admin'])
                        && $this->record->reservation_status === 'pendiente';
                })
                ->action(function (): void {
                    $this->record->update([
                        'reservation_status' => 'confirmada',
                    ]);

                    $this->record->refresh();
                }),

            Action::make('cancelReservation')
                ->label('Cancelar cita')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar cita')
                ->modalDescription('Esto cancelará la reserva. Si estaba bloqueando un horario, ese horario volverá a quedar disponible.')
                ->modalSubmitActionLabel('Sí, cancelar cita')
                ->visible(function (): bool {
                    return auth()->user()?->hasAnyRole(['owner', 'admin'])
                        && $this->record->reservation_status !== 'cancelada';
                })
                ->action(function (): void {
                    $this->record->update([
                        'reservation_status' => 'cancelada',
                    ]);

                    $this->record->refresh();
                }),
        ];
    }
}
