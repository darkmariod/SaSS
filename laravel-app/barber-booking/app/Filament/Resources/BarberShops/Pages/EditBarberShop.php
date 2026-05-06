<?php

namespace App\Filament\Resources\BarberShops\Pages;

use App\Filament\Resources\BarberShops\BarberShopResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBarberShop extends EditRecord
{
    protected static string $resource = BarberShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar'),
        ];
    }
}
