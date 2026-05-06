<?php

namespace App\Filament\Resources\BarberPayments\Pages;

use App\Filament\Resources\BarberPayments\BarberPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBarberPayment extends CreateRecord
{
    protected static string $resource = BarberPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = $data['status'] ?? 'draft';
        $data['gross_amount'] = 0;
        $data['commission_amount'] = 0;
        $data['net_amount'] = 0;
        $data['reservations_count'] = 0;
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->calculate();
    }
}
