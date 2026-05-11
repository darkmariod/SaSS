<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Filament\Resources\Reservations\Pages\ViewReservation;
use App\Filament\Resources\Reservations\Schemas\ReservationForm;
use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Reservas';

    protected static ?string $modelLabel = 'Reserva';

    protected static ?string $pluralModelLabel = 'Reservas';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin', 'barber']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'barberShop',
                'service',
                'addonService',
                'consultant',
                'detail',
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('barber')) {
            return $query->where('consultant_id', $user->id);
        }

        if ($user->hasRole('owner')) {
            return $query->whereHas('barberShop', function (Builder $builder) use ($user) {
                $builder->where('owner_id', $user->id);
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return ReservationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la reserva')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('customer_name')
                                    ->label('Cliente')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('customer_phone')
                                    ->label('Teléfono'),

                                TextEntry::make('customer_email')
                                    ->label('Correo')
                                    ->placeholder('Sin correo'),

                                TextEntry::make('barberShop.name')
                                    ->label('Barbería'),

                                TextEntry::make('service.name')
                                    ->label('Servicio')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('addonService.name')
                                    ->label('Extra')
                                    ->placeholder('Sin extra'),

                                TextEntry::make('consultant.name')
                                    ->label('Barbero'),

                                TextEntry::make('reservation_date')
                                    ->label('Fecha')
                                    ->date('d/m/Y'),

                                TextEntry::make('start_time')
                                    ->label('Inicio'),

                                TextEntry::make('end_time')
                                    ->label('Fin'),

                                TextEntry::make('total_amount')
                                    ->label('Total')
                                    ->money('USD'),

                                TextEntry::make('reservation_status')
                                    ->label('Estado reserva')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pendiente' => 'warning',
                                        'confirmada' => 'success',
                                        'completada' => 'info',
                                        'cancelada' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('payment_status')
                                    ->label('Estado pago')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pendiente' => 'warning',
                                        'comprobante_subido' => 'info',
                                        'pagado' => 'success',
                                        'rechazado' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Section::make('Comprobante de pago')
                    ->schema([
                        TextEntry::make('detail.receipt_image')
                            ->label('Archivo')
                            ->placeholder('Sin comprobante'),

                        TextEntry::make('receipt_link')
                            ->label('Ver comprobante')
                            ->state(function (Reservation $record): string {
                                if (! $record->detail?->receipt_image) {
                                    return 'Sin comprobante';
                                }

                                return asset('storage/' . $record->detail->receipt_image);
                            })
                            ->url(function (Reservation $record): ?string {
                                if (! $record->detail?->receipt_image) {
                                    return null;
                                }

                                return asset('storage/' . $record->detail->receipt_image);
                            })
                            ->openUrlInNewTab(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('reservation_date', 'desc')
            ->columns([
                TextColumn::make('reservation_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Hora')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight(FontWeight::Bold),
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
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('reservation_status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => '⏳ Pendiente',
                        'confirmada' => '✅ Confirmada',
                        'completada' => '✅ Completada',
                        'cancelada' => '❌ Cancelada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'confirmada' => 'success',
                        'completada' => 'info',
                        'cancelada' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => '💰 Pendiente',
                        'comprobante_subido' => '📄 Por revisar',
                        'pagado' => '✅ Pagado',
                        'rechazado' => '❌ Rechazado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'comprobante_subido' => 'info',
                        'pagado' => 'success',
                        'rechazado' => 'danger',
                        default => 'gray',
                    }),
                ImageColumn::make('detail.receipt_image')
                    ->label('Comprobante')
                    ->disk('public')
                    ->height(48)
                    ->square()
                    ->extraImgAttributes(['class' => 'rounded-lg']),
            ])
            ->filters([
                SelectFilter::make('reservation_status')
                    ->label('Estado reserva')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Estado pago')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'comprobante_subido' => 'Comprobante subido',
                        'pagado' => 'Pagado',
                        'rechazado' => 'Rechazado',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver'),
                Action::make('approvePayment')
                    ->label('Aprobar pago')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Reservation $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->payment_status === 'comprobante_subido';
                    })
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'payment_status' => 'pagado',
                            'reservation_status' => 'confirmada',
                        ]);
                        $record->detail?->update([
                            'payment_status' => 'pagado',
                            'paid_at' => now(),
                        ]);

                        \App\Models\CashRegister::query()
                            ->where('barber_shop_id', $record->barber_shop_id)
                            ->whereDate('date', $record->reservation_date)
                            ->first()
                            ?->recalculate();
                    }),
                Action::make('rejectPayment')
                    ->label('Rechazar pago')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (Reservation $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->payment_status === 'comprobante_subido';
                    })
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'payment_status' => 'rechazado',
                            'reservation_status' => 'cancelada',
                        ]);
                        $record->detail?->update([
                            'payment_status' => 'rechazado',
                        ]);

                        \App\Models\CashRegister::query()
                            ->where('barber_shop_id', $record->barber_shop_id)
                            ->whereDate('date', $record->reservation_date)
                            ->first()
                            ?->recalculate();
                    }),
                Action::make('confirmReservation')
                    ->label('Confirmar')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Reservation $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->reservation_status === 'pendiente';
                    })
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'reservation_status' => 'confirmada',
                        ]);
                    }),
                Action::make('cancelReservation')
                    ->label('Cancelar')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (Reservation $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->reservation_status !== 'cancelada';
                    })
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'reservation_status' => 'cancelada',
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'view' => ViewReservation::route('/{record}'),
            'edit' => EditReservation::route('/{record}/edit'),
        ];
    }
}
