<?php

namespace App\Filament\Resources\BarberPayments\Pages;

use App\Filament\Resources\BarberPayments\BarberPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBarberPayments extends ListRecords
{
    protected static string $resource = BarberPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear pago'),
        ];
    }
}
