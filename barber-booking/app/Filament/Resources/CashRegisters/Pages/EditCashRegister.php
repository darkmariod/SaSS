<?php

namespace App\Filament\Resources\CashRegisters\Pages;

use App\Filament\Resources\CashRegisters\CashRegisterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashRegister extends EditRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->visible(fn (): bool => auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'closed' && empty($this->record->closed_by)) {
            $data['closed_by'] = auth()->id();
            $data['closed_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->recalculate();
    }
}
