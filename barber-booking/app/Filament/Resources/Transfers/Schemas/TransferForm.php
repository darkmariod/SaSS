<?php

namespace App\Filament\Resources\Transfers\Schemas;

use App\Models\BarberShop;
use App\Models\Reservation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('barber_shop_id')
                    ->label('Barbería')
                    ->options(function (): array {
                        $user = auth()->user();

                        if ($user?->hasRole('owner')) {
                            return BarberShop::query()
                                ->where('owner_id', $user->id)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        }

                        return BarberShop::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('reservation_id')
                    ->label('Reserva')
                    ->options(function (callable $get): array {
                        $shopId = $get('barber_shop_id');

                        if (! $shopId) {
                            return [];
                        }

                        return Reservation::query()
                            ->where('barber_shop_id', $shopId)
                            ->orderBy('reservation_date', 'desc')
                            ->get()
                            ->mapWithKeys(fn (Reservation $r) => [
                                $r->id => "#{$r->id} - {$r->customer_name} ({$r->reservation_date})",
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('reference')
                    ->label('Referencia')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('amount')
                    ->label('Monto')
                    ->prefix('$')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull()
                    ->nullable(),

                FileUpload::make('receipt_image_path')
                    ->label('Comprobante')
                    ->image()
                    ->disk('public')
                    ->directory('receipts')
                    ->nullable(),
            ]);
    }
}
