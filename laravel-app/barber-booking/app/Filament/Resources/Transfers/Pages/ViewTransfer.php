<?php

namespace App\Filament\Resources\Transfers\Pages;

use App\Filament\Resources\Transfers\TransferResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTransfer extends ViewRecord
{
    protected static string $resource = TransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openReceipt')
                ->label('Ver comprobante')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->url(function (): ?string {
                    if (! $this->record->receipt_image_path) {
                        return null;
                    }

                    return asset('storage/' . $this->record->receipt_image_path);
                })
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->receipt_image_path)),

            Action::make('confirmTransfer')
                ->label('Confirmar transferencia')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmar transferencia')
                ->modalDescription('Esto marcará la transferencia como confirmada, el pago como pagado y la reserva como confirmada.')
                ->modalSubmitActionLabel('Sí, confirmar')
                ->visible(function (): bool {
                    return auth()->user()?->hasAnyRole(['owner', 'admin'])
                        && $this->record->status === 'pending';
                })
                ->action(function (): void {
                    $this->record->confirm(auth()->id());
                    $this->record->refresh();
                }),

            Action::make('rejectTransfer')
                ->label('Rechazar transferencia')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechazar transferencia')
                ->modalDescription('Esto marcará la transferencia como rechazada y cancelará la reserva asociada.')
                ->modalSubmitActionLabel('Sí, rechazar')
                ->visible(function (): bool {
                    return auth()->user()?->hasAnyRole(['owner', 'admin'])
                        && $this->record->status === 'pending';
                })
                ->action(function (): void {
                    $this->record->reject(auth()->id());
                    $this->record->refresh();
                }),
        ];
    }
}
