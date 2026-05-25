<?php

namespace App\Filament\Resources\BarberShops\Pages;

use App\Filament\Resources\BarberShops\BarberShopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBarberShop extends CreateRecord
{
    protected static string $resource = BarberShopResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si el usuario es owner, se asigna automáticamente como dueño
        if (! isset($data['owner_id']) && auth()->user()?->hasRole('owner')) {
            $data['owner_id'] = auth()->id();
        }

        // Auto-generar slug desde el nombre si no se especificó
        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = str($data['name'])->slug()->toString();
        }

        return $data;
    }
}
