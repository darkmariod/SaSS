<?php

namespace App\Filament\Resources\Reservations\Schemas;

use App\Models\BarberProfile;
use App\Models\BarberShop;
use App\Models\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
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
                            ->preload()
                            ->live(),

                        Select::make('consultant_id')
                            ->label('Barbero')
                            ->options(function (callable $get): array {
                                $shopId = $get('barber_shop_id');

                                if (! $shopId) {
                                    return [];
                                }

                                return BarberProfile::query()
                                    ->where('barber_shop_id', $shopId)
                                    ->where('is_active', true)
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(fn (BarberProfile $p) => [
                                        $p->user_id => $p->display_name,
                                    ])
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('service_id')
                            ->label('Servicio')
                            ->options(function (callable $get): array {
                                $shopId = $get('barber_shop_id');

                                if (! $shopId) {
                                    return [];
                                }

                                return Service::query()
                                    ->where('barber_shop_id', $shopId)
                                    ->whereIn('service_type', ['main', 'option'])
                                    ->where('is_active', true)
                                    ->orderBy('category')
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn (Service $s) => [
                                        $s->id => $s->name . ' ($' . number_format($s->price, 2) . ')',
                                    ])
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('addon_service_id')
                            ->label('Servicio adicional')
                            ->options(function (callable $get): array {
                                $shopId = $get('barber_shop_id');

                                if (! $shopId) {
                                    return [];
                                }

                                return Service::query()
                                    ->where('barber_shop_id', $shopId)
                                    ->where('service_type', 'addon')
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(fn (Service $s) => [
                                        $s->id => $s->name . ' ($' . number_format($s->price, 2) . ')',
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),

                Grid::make(3)
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Cliente')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('customer_phone')
                            ->label('Teléfono')
                            ->required()
                            ->maxLength(30),

                        TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                    ]),

                Grid::make(3)
                    ->schema([
                        DatePicker::make('reservation_date')
                            ->label('Fecha')
                            ->required(),

                        TextInput::make('start_time')
                            ->label('Hora inicio')
                            ->required()
                            ->placeholder('09:00'),

                        TextInput::make('total_amount')
                            ->label('Total')
                            ->prefix('$')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),
                    ]),

                Grid::make(2)
                    ->schema([
                        Select::make('reservation_status')
                            ->label('Estado reserva')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'confirmada' => 'Confirmada',
                                'completada' => 'Completada',
                                'cancelada' => 'Cancelada',
                            ])
                            ->default('pendiente')
                            ->required(),

                        Select::make('payment_status')
                            ->label('Estado pago')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'comprobante_subido' => 'Comprobante subido',
                                'pagado' => 'Pagado',
                                'rechazado' => 'Rechazado',
                            ])
                            ->default('pendiente')
                            ->required(),
                    ]),
            ]);
    }
}
