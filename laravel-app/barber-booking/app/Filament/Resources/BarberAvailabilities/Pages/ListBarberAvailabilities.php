<?php

namespace App\Filament\Resources\BarberAvailabilities\Pages;

use App\Filament\Resources\BarberAvailabilities\BarberAvailabilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBarberAvailabilities extends ListRecords
{
    protected static string $resource = BarberAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear horario'),
        ];
    }
}
