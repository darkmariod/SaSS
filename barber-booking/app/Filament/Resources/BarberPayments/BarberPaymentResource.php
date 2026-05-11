<?php

namespace App\Filament\Resources\BarberPayments;

use App\Filament\Resources\BarberPayments\Pages\CreateBarberPayment;
use App\Filament\Resources\BarberPayments\Pages\EditBarberPayment;
use App\Filament\Resources\BarberPayments\Pages\ListBarberPayments;
use App\Models\BarberPayment;
use App\Models\BarberProfile;
use App\Models\BarberShop;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BarberPaymentResource extends Resource
{
    protected static ?string $model = BarberPayment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Pagos a barberos';

    protected static ?string $modelLabel = 'Pago a barbero';

    protected static ?string $pluralModelLabel = 'Pagos a barberos';

    protected static ?int $navigationSort = 9;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
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
                'barberProfile.user',
                'calculatedBy',
                'paidBy',
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
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
        return $schema
            ->components([
                Section::make('Periodo y barbero')
                    ->schema([
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
                                    ->default(function (): ?int {
                                        $user = auth()->user();
                                        if (! $user?->hasRole('owner')) {
                                            return null;
                                        }
                                        return BarberShop::query()
                                            ->where('owner_id', $user->id)
                                            ->value('id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('barber_profile_id')
                                    ->label('Barbero')
                                    ->options(function (): array {
                                        $user = auth()->user();
                                        $query = BarberProfile::query()
                                            ->with('barberShop')
                                            ->where('is_active', true);

                                        if ($user?->hasRole('owner')) {
                                            $query->whereHas('barberShop', function (Builder $builder) use ($user) {
                                                $builder->where('owner_id', $user->id);
                                            });
                                        }

                                        return $query
                                            ->orderBy('display_name')
                                            ->get()
                                            ->mapWithKeys(function (BarberProfile $barberProfile): array {
                                                return [
                                                    $barberProfile->id => $barberProfile->display_name . ' — ' . $barberProfile->barberShop?->name,
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                DatePicker::make('period_start')
                                    ->label('Desde')
                                    ->required(),

                                DatePicker::make('period_end')
                                    ->label('Hasta')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Ajustes del pago')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('advance_amount')
                                    ->label('Adelantos')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),

                                TextInput::make('incentive_amount')
                                    ->label('Incentivos')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),
                            ]),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->placeholder('Ej: adelanto de la semana, bono por meta, etc.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Resultado calculado')
                    ->hidden(fn ($livewire) => $livewire instanceof CreateRecord)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('gross_amount')
                                    ->label('Total facturado')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('commission_percentage')
                                    ->label('% comisión')
                                    ->suffix('%')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('commission_amount')
                                    ->label('Comisión')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('reservations_count')
                                    ->label('Citas')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('net_amount')
                                    ->label('Neto a pagar')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'draft' => 'Borrador',
                                        'calculated' => 'Calculado',
                                        'paid' => 'Pagado',
                                        'cancelled' => 'Cancelado',
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('period_start')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('period_end')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('barberProfile.display_name')
                    ->label('Barbero')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('barberShop.name')
                    ->label('Barbería')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reservations_count')
                    ->label('Citas')
                    ->sortable(),

                TextColumn::make('gross_amount')
                    ->label('Facturado')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('commission_percentage')
                    ->label('%')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('commission_amount')
                    ->label('Comisión')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('advance_amount')
                    ->label('Adelantos')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('incentive_amount')
                    ->label('Incentivos')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('net_amount')
                    ->label('Neto')
                    ->money('USD')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Borrador',
                        'calculated' => 'Calculado',
                        'paid' => 'Pagado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'calculated' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('paid_at')
                    ->label('Pagado el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'calculated' => 'Calculado',
                        'paid' => 'Pagado',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),

                Action::make('calculate')
                    ->label('Calcular')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Calcular pago')
                    ->modalDescription('Esto calculará el total facturado, comisión y neto a pagar según las reservas pagadas del periodo.')
                    ->modalSubmitActionLabel('Sí, calcular')
                    ->visible(function (BarberPayment $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && in_array($record->status, ['draft', 'calculated'], true);
                    })
                    ->action(function (BarberPayment $record): void {
                        $record->calculate();
                    }),

                Action::make('markAsPaid')
                    ->label('Marcar pagado')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Marcar pago como pagado')
                    ->modalDescription('Esto marcará este pago como pagado al barbero.')
                    ->modalSubmitActionLabel('Sí, marcar pagado')
                    ->visible(function (BarberPayment $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->status === 'calculated';
                    })
                    ->action(function (BarberPayment $record): void {
                        $record->markAsPaid(auth()->id());
                    }),

                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(function (BarberPayment $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->status !== 'paid'
                            && $record->status !== 'cancelled';
                    })
                    ->action(function (BarberPayment $record): void {
                        $record->cancel();
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
            'index' => ListBarberPayments::route('/'),
            'create' => CreateBarberPayment::route('/create'),
            'edit' => EditBarberPayment::route('/{record}/edit'),
        ];
    }
}
