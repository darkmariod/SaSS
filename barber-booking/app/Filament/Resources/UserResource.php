<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información del Usuario')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255),

                        Select::make('roles')
                            ->label('Rol')
                            ->relationship('roles', 'name')
                            ->options(Role::whereIn('name', ['owner', 'admin', 'barber'])->pluck('name', 'name'))
                            ->required()
                            ->native(false),

                        Select::make('barber_shop_id')
                            ->label('Barbería (solo para barberos)')
                            ->options(function () {
                                $user = auth()->user();
                                if ($user?->hasRole('owner')) {
                                    return \App\Models\BarberShop::query()
                                        ->where('owner_id', $user->id)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                return \App\Models\BarberShop::query()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->visible(function ($component) {
                                $roles = $component->getLivewire()->data['roles'] ?? [];
                                return in_array('barber', (array) $roles);
                            })
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'owner' => 'Dueño',
                        'admin' => 'Administrador',
                        'barber' => 'Barbero',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'owner' => 'success',
                        'admin' => 'info',
                        'barber' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\UserResource\Pages\ListUsers::route('/'),
            'create' => \App\Filament\Resources\UserResource\Pages\CreateUser::route('/create'),
            'edit' => \App\Filament\Resources\UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
    }
}
