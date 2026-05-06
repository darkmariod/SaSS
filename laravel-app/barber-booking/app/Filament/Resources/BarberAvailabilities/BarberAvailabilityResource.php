<?php

namespace App\Filament\Resources\BarberAvailabilities;

use App\Filament\Resources\BarberAvailabilities\Pages\CreateBarberAvailability;
use App\Filament\Resources\BarberAvailabilities\Pages\EditBarberAvailability;
use App\Filament\Resources\BarberAvailabilities\Pages\ListBarberAvailabilities;
use App\Models\BarberAvailability;
use App\Models\BarberProfile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
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

class BarberAvailabilityResource extends Resource
{
    protected static ?string $model = BarberAvailability::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Horarios';

    protected static ?string $modelLabel = 'Horario';

    protected static ?string $pluralModelLabel = 'Horarios';

    protected static ?int $navigationSort = 4;

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
            ->with(['barberProfile.user', 'barberProfile.barberShop']);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('owner')) {
            return $query->whereHas('barberProfile.barberShop', function (Builder $builder) use ($user) {
                $builder->where('owner_id', $user->id);
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Disponibilidad semanal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
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

                                Select::make('day_of_week')
                                    ->label('Día de la semana')
                                    ->options([
                                        0 => 'Domingo',
                                        1 => 'Lunes',
                                        2 => 'Martes',
                                        3 => 'Miércoles',
                                        4 => 'Jueves',
                                        5 => 'Viernes',
                                        6 => 'Sábado',
                                    ])
                                    ->required(),

                                TimePicker::make('start_time')
                                    ->label('Hora inicio')
                                    ->seconds(false)
                                    ->required(),

                                TimePicker::make('end_time')
                                    ->label('Hora fin')
                                    ->seconds(false)
                                    ->required()
                                    ->after('start_time'),

                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('day_of_week')
            ->columns([
                TextColumn::make('barberProfile.display_name')
                    ->label('Barbero')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('barberProfile.barberShop.name')
                    ->label('Barbería')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('day_of_week')
                    ->label('Día')
                    ->badge()
                    ->formatStateUsing(fn (int|string $state): string => match ((int) $state) {
                        0 => 'Domingo',
                        1 => 'Lunes',
                        2 => 'Martes',
                        3 => 'Miércoles',
                        4 => 'Jueves',
                        5 => 'Viernes',
                        6 => 'Sábado',
                        default => 'Desconocido',
                    })
                    ->color(fn (int|string $state): string => match ((int) $state) {
                        0, 6 => 'warning',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Fin')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Día')
                    ->options([
                        0 => 'Domingo',
                        1 => 'Lunes',
                        2 => 'Martes',
                        3 => 'Miércoles',
                        4 => 'Jueves',
                        5 => 'Viernes',
                        6 => 'Sábado',
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
            'index' => ListBarberAvailabilities::route('/'),
            'create' => CreateBarberAvailability::route('/create'),
            'edit' => EditBarberAvailability::route('/{record}/edit'),
        ];
    }
}
