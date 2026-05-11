<?php

namespace App\Filament\Resources\BarberProfiles\Pages;

use App\Filament\Resources\BarberProfiles\BarberProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBarberProfiles extends ListRecords
{
    protected static string $resource = BarberProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear barbero'),
        ];
    }
}
