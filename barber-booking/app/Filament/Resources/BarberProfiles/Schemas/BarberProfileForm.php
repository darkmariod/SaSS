<?php

namespace App\Filament\Resources\BarberProfiles\Schemas;

use App\Models\BarberShop;
use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BarberProfileForm
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

                Select::make('user_id')
                    ->label('Usuario')
                    ->options(fn (): array => User::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Cuenta con la que este barbero entra al sistema.'),

                TextInput::make('display_name')
                    ->label('Nombre visible')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Así lo ven los clientes al reservar.'),

                Textarea::make('bio')
                    ->label('Descripción')
                    ->rows(2)
                    ->maxLength(500),

                TextInput::make('google_calendar_id')
                    ->label('Google Calendar del barbero')
                    ->maxLength(255)
                    ->placeholder('correo@gmail.com  o  ...@group.calendar.google.com')
                    ->helperText(
                        'ID del calendario donde se copian sus reservas. Se toma de '
                        . 'Google Calendar → Configuración del calendario → ID del calendario. '
                        . 'Ese calendario debe estar compartido con la cuenta de servicio, con '
                        . 'permiso para hacer cambios en los eventos. Si se deja vacío, se usa '
                        . 'el calendario general de la barbería.'
                    ),

                TextInput::make('commission_percentage')
                    ->label('Comisión (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(50)
                    ->required()
                    ->suffix('%'),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->helperText('Si se apaga, deja de aparecer en la reserva pública.'),
            ]);
    }
}
