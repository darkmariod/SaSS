<?php

namespace App\Filament\Resources\BarberProfiles\Pages;

use App\Filament\Resources\BarberProfiles\BarberProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBarberProfile extends EditRecord
{
    protected static string $resource = BarberProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar'),
        ];
    }
}
