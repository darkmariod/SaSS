<?php

namespace App\Filament\Resources\BarberPayments\Pages;

use App\Filament\Resources\BarberPayments\BarberPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBarberPayment extends EditRecord
{
    protected static string $resource = BarberPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->visible(fn (): bool => auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->status !== 'paid') {
            $this->record->calculate();
        }
    }
}
