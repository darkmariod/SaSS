<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\BarberShop;
use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scissors';

    protected static ?string $navigationLabel = 'Servicios';

    protected static ?string $modelLabel = 'Servicio';

    protected static ?string $pluralModelLabel = 'Servicios';

    protected static ?int $navigationSort = 2;

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
            ->with(['barberShop', 'parentService']);

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
                Section::make('Información del servicio')
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
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        }

                                        return BarberShop::query()
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

                                Select::make('service_type')
                                    ->label('Tipo de servicio')
                                    ->options([
                                        'main' => 'Servicio principal',
                                        'option' => 'Opción reservable',
                                        'addon' => 'Extra / adicional',
                                    ])
                                    ->required()
                                    ->live(),

                                Select::make('parent_service_id')
                                    ->label('Servicio padre')
                                    ->helperText('Úsalo solo cuando el tipo sea opción reservable.')
                                    ->options(function (): array {
                                        $user = auth()->user();

                                        $query = Service::query()
                                            ->where('service_type', 'main')
                                            ->where('is_active', true);

                                        if ($user?->hasRole('owner')) {
                                            $query->whereHas('barberShop', function (Builder $builder) use ($user) {
                                                $builder->where('owner_id', $user->id);
                                            });
                                        }

                                        return $query
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                TextInput::make('category')
                                    ->label('Categoría')
                                    ->placeholder('Ej: Men')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->placeholder('Ej: Fade')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('duration_minutes')
                                    ->label('Duración en minutos')
                                    ->numeric()
                                    ->minValue(5)
                                    ->step(5)
                                    ->required(),

                                TextInput::make('price')
                                    ->label('Precio')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),

                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('barberShop.name')
                    ->label('Barbería')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'main' => 'Principal',
                        'option' => 'Opción',
                        'addon' => 'Extra',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'main' => 'gray',
                        'option' => 'info',
                        'addon' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('parentService.name')
                    ->label('Padre')
                    ->placeholder('—'),

                TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('service_type')
                    ->label('Tipo')
                    ->options([
                        'main' => 'Principal',
                        'option' => 'Opción',
                        'addon' => 'Extra',
                    ]),

                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(function (): array {
                        return Service::query()
                            ->select('category')
                            ->distinct()
                            ->orderBy('category')
                            ->pluck('category', 'category')
                            ->toArray();
                    }),
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
