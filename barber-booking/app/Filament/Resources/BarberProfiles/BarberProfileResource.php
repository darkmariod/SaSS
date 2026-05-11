<?php

namespace App\Filament\Resources\BarberProfiles;

use App\Filament\Resources\BarberProfiles\Pages\CreateBarberProfile;
use App\Filament\Resources\BarberProfiles\Pages\EditBarberProfile;
use App\Filament\Resources\BarberProfiles\Pages\ListBarberProfiles;
use App\Models\BarberProfile;
use App\Models\BarberShop;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BarberProfileResource extends Resource
{
    protected static ?string $model = BarberProfile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Barberos';

    protected static ?string $modelLabel = 'Barbero';

    protected static ?string $pluralModelLabel = 'Barberos';

    protected static ?int $navigationSort = 3;

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
            ->with(['barberShop', 'user']);

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
                Section::make('Información del barbero')
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

                                Select::make('user_id')
                                    ->label('Usuario barbero')
                                    ->helperText('Solo usuarios con rol "barbero". Si no aparece, créalo y asignale el rol.')
                                    ->options(function (): array {
                                        return User::query()
                                            ->whereHas('roles', function ($query) {
                                                $query->where('name', 'barber');
                                            })
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('display_name')
                                    ->label('Nombre visible')
                                    ->placeholder('Ej: Jean Barber')
                                    ->required()
                                    ->maxLength(255),

                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),

                                FileUpload::make('photo')
                                    ->label('Foto')
                                    ->disk('public')
                                    ->directory('barbers')
                                    ->image()
                                    ->imageEditor()
                                    ->nullable()
                                    ->columnSpanFull(),
                            ]),

                        Textarea::make('bio')
                            ->label('Descripción / bio')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('display_name')
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->height(48)
                    ->square()
                    ->circular(),

                TextColumn::make('display_name')
                    ->label('Barbero')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('barberShop.name')
                    ->label('Barbería')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
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
            'index' => ListBarberProfiles::route('/'),
            'create' => CreateBarberProfile::route('/create'),
            'edit' => EditBarberProfile::route('/{record}/edit'),
        ];
    }
}
