<?php

namespace App\Filament\Resources\CashMovements\Pages;

use App\Filament\Resources\CashMovements\CashMovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashMovement extends CreateRecord
{
    protected static string $resource = CashMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['performed_by'] = auth()->id();

        return $data;
    }
}
