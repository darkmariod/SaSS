<?php

namespace App\Filament\Resources\CashRegisters\Pages;

use App\Filament\Resources\CashRegisters\CashRegisterResource;
use App\Models\CashRegister;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;

class CreateCashRegister extends CreateRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['opened_by'] = auth()->id();
        $data['opened_at'] = now();
        $data['status'] = 'open';

        $openingAmount = (float) ($data['opening_amount'] ?? 0);

        $data['cash_income_amount'] = 0;
        $data['transfer_income_amount'] = 0;
        $data['manual_income_amount'] = 0;
        $data['expense_amount'] = 0;
        $data['system_amount'] = $openingAmount;
        $data['difference_amount'] = 0;

        return $data;
    }

    protected function handleRecordCreation(array $data): CashRegister
    {
        return CashRegister::firstOrCreate(
            [
                'barber_shop_id' => $data['barber_shop_id'],
                'date' => $data['date'],
            ],
            $data
        );
    }

    protected function afterCreate(): void
    {
        $this->record->recalculate();
    }
}
