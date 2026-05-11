<?php

namespace App\Filament\Resources\BarberAvailabilities\Pages;

use App\Filament\Resources\BarberAvailabilities\BarberAvailabilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBarberAvailability extends EditRecord
{
    protected static string $resource = BarberAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar'),
        ];
    }
}
