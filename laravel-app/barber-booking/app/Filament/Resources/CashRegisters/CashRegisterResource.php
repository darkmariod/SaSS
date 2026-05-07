<?php

namespace App\Filament\Resources\CashRegisters;

use App\Filament\Resources\CashRegisters\Pages\CreateCashRegister;
use App\Filament\Resources\CashRegisters\Pages\EditCashRegister;
use App\Filament\Resources\CashRegisters\Pages\ListCashRegisters;
use App\Models\BarberShop;
use App\Models\CashRegister;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Caja diaria';

    protected static ?string $modelLabel = 'Caja';

    protected static ?string $pluralModelLabel = 'Cajas';

    protected static ?int $navigationSort = 7;

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
            ->with(['barberShop', 'openedBy', 'closedBy']);

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

    public static function getRelations(): array
    {
        return [
            RelationManagers\MovementsRelationManager::class,
            RelationManagers\TransfersRelationManager::class,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Apertura / cierre de caja')
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

                                DatePicker::make('date')
                                    ->label('Fecha')
                                    ->default(now())
                                    ->required(),

                                TextInput::make('opening_amount')
                                    ->label('Monto inicial')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),

                                TextInput::make('real_amount')
                                    ->label('Monto real contado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->nullable(),

                                Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'open' => 'Abierta',
                                        'closed' => 'Cerrada',
                                    ])
                                    ->default('open')
                                    ->required(),
                            ]),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Totales del sistema')
                    ->description('Estos valores se recalculan con reservas pagadas, transferencias confirmadas y movimientos manuales.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('cash_income_amount')
                                    ->label('Efectivo sistema')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('transfer_income_amount')
                                    ->label('Transferencias')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('manual_income_amount')
                                    ->label('Ingresos manuales')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('expense_amount')
                                    ->label('Egresos')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('system_amount')
                                    ->label('Total sistema')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('difference_amount')
                                    ->label('Diferencia')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('barberShop.name')
                    ->label('Barbería')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('opening_amount')
                    ->label('Inicial')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('cash_income_amount')
                    ->label('Efectivo')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('transfer_income_amount')
                    ->label('Transferencias')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('manual_income_amount')
                    ->label('Ingresos')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('expense_amount')
                    ->label('Egresos')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('system_amount')
                    ->label('Sistema')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('real_amount')
                    ->label('Real')
                    ->money('USD')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('difference_amount')
                    ->label('Diferencia')
                    ->money('USD')
                    ->sortable()
                    ->color(fn ($state): string => (float) $state === 0.0 ? 'success' : 'danger'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Abierta',
                        'closed' => 'Cerrada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('openedBy.name')
                    ->label('Abrió')
                    ->placeholder('—'),

                TextColumn::make('closedBy.name')
                    ->label('Cerró')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierta',
                        'closed' => 'Cerrada',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),

                Action::make('recalculate')
                    ->label('Recalcular')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (CashRegister $record): void {
                        $record->recalculate();
                    }),

                Action::make('closeRegister')
                    ->label('Cerrar caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (CashRegister $record): bool => $record->status === 'open')
                    ->requiresConfirmation()
                    ->schema([
                        TextInput::make('real_amount')
                            ->label('Monto real contado')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notas de cierre')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->action(function (CashRegister $record, array $data): void {
                        if (! empty($data['notes'])) {
                            $record->update([
                                'notes' => $data['notes'],
                            ]);
                        }

                        $record->close((float) $data['real_amount'], auth()->id());
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
            'index' => ListCashRegisters::route('/'),
            'create' => CreateCashRegister::route('/create'),
            'edit' => EditCashRegister::route('/{record}/edit'),
        ];
    }
}
