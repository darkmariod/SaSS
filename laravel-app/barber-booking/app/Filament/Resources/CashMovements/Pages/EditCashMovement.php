<?php

namespace App\Filament\Resources\CashMovements\Pages;

use App\Filament\Resources\CashMovements\CashMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashMovement extends EditRecord
{
    protected static string $resource = CashMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->visible(fn (): bool => auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false),
        ];
    }
}
