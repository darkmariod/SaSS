<?php

namespace App\Filament\Widgets;

use App\Models\BarberShop;
use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TodayReservationsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Reservas de hoy';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin', 'barber']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getReservationsQuery())
            ->defaultSort('start_time')
            ->columns([
                TextColumn::make('start_time')
                    ->label('Hora')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('customer_phone')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('service.name')
                    ->label('Servicio')
                    ->searchable(),

                TextColumn::make('addonService.name')
                    ->label('Extra')
                    ->placeholder('—'),

                TextColumn::make('consultant.name')
                    ->label('Barbero')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD'),

                TextColumn::make('reservation_status')
                    ->label('Reserva')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'cancelada' => 'Cancelada',
                        'completada' => 'Completada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'confirmada' => 'success',
                        'cancelada' => 'danger',
                        'completada' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'comprobante_subido' => 'Comprobante subido',
                        'pagado' => 'Pagado',
                        'rechazado' => 'Rechazado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'comprobante_subido' => 'info',
                        'pagado' => 'success',
                        'rechazado' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('markCompleted')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Reservation $record): bool {
                        $user = auth()->user();
                        if (! $user) {
                            return false;
                        }
                        // Owner/admin pueden marcar cualquier reserva confirmada
                        if ($user->hasAnyRole(['owner', 'admin'])) {
                            return $record->reservation_status === 'confirmada';
                        }
                        // Barber solo puede marcar SUS reservas confirmadas
                        if ($user->hasRole('barber')) {
                            return $record->reservation_status === 'confirmada'
                                && $record->consultant_id === $user->id;
                        }
                        return false;
                    })
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'reservation_status' => 'completada',
                        ]);
                    }),

                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (Reservation $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->reservation_status !== 'cancelada'
                            && $record->reservation_status !== 'completada';
                    })
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'reservation_status' => 'cancelada',
                        ]);
                    }),
            ]);
    }

    private function getReservationsQuery(): Builder
    {
        $query = Reservation::query()
            ->with(['service', 'addonService', 'consultant', 'barberShop'])
            ->whereDate('reservation_date', today());

        $user = auth()->user();

        if ($user?->hasRole('barber')) {
            return $query->where('consultant_id', $user->id);
        }

        return $query->whereIn('barber_shop_id', $this->getVisibleShopIds());
    }

    private function getVisibleShopIds(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        if ($user->hasRole('owner')) {
            return BarberShop::query()
                ->where('owner_id', $user->id)
                ->pluck('id');
        }

        if ($user->hasRole('barber')) {
            return BarberShop::query()
                ->whereHas('barberProfiles', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->pluck('id');
        }

        return BarberShop::query()->pluck('id');
    }
}
