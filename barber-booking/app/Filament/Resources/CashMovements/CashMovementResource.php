<?php

namespace App\Filament\Resources\CashMovements;

use App\Filament\Resources\CashMovements\Pages\CreateCashMovement;
use App\Filament\Resources\CashMovements\Pages\EditCashMovement;
use App\Filament\Resources\CashMovements\Pages\ListCashMovements;
use App\Models\CashMovement;
use App\Models\CashRegister;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class CashMovementResource extends Resource
{
    protected static ?string $model = CashMovement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Movimientos de caja';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $pluralModelLabel = 'Movimientos de caja';

    protected static ?int $navigationSort = 8;

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
                'cashRegister.barberShop',
                'performedBy',
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('owner')) {
            return $query->whereHas('cashRegister.barberShop', function (Builder $builder) use ($user) {
                $builder->where('owner_id', $user->id);
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Movimiento de caja')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('cash_register_id')
                                    ->label('Caja')
                                    ->options(function (): array {
                                        $user = auth()->user();

                                        $query = CashRegister::query()
                                            ->with('barberShop')
                                            ->where('status', 'open');

                                        if ($user?->hasRole('owner')) {
                                            $query->whereHas('barberShop', function (Builder $builder) use ($user) {
                                                $builder->where('owner_id', $user->id);
                                            });
                                        }

                                        return $query
                                            ->orderByDesc('date')
                                            ->get()
                                            ->mapWithKeys(function (CashRegister $register): array {
                                                return [
                                                    $register->id => $register->barberShop?->name . ' — ' . $register->date->format('d/m/Y') . ' — Caja abierta',
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'income' => 'Ingreso',
                                        'expense' => 'Egreso',
                                    ])
                                    ->required(),

                                TextInput::make('amount')
                                    ->label('Monto')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->required(),

                                Textarea::make('description')
                                    ->label('Descripción')
                                    ->placeholder('Ej: compra de gel, ingreso extra, gasto de limpieza...')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('cashRegister.barberShop.name')
                    ->label('Barbería')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cashRegister.date')
                    ->label('Caja')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'income' => 'Ingreso',
                        'expense' => 'Egreso',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('performedBy.name')
                    ->label('Registrado por')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'income' => 'Ingreso',
                        'expense' => 'Egreso',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
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
            'index' => ListCashMovements::route('/'),
            'create' => CreateCashMovement::route('/create'),
            'edit' => EditCashMovement::route('/{record}/edit'),
        ];
    }
}
