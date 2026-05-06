<?php

namespace App\Filament\Resources\BarberShops\Pages;

use App\Filament\Resources\BarberShops\BarberShopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBarberShops extends ListRecords
{
    protected static string $resource = BarberShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear barbería'),
        ];
    }
}
