<?php

namespace App\Filament\Resources\BarberShops;

use App\Filament\Resources\BarberShops\Pages\CreateBarberShop;
use App\Filament\Resources\BarberShops\Pages\EditBarberShop;
use App\Filament\Resources\BarberShops\Pages\ListBarberShops;
use App\Models\BarberShop;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BarberShopResource extends Resource
{
    protected static ?string $model = BarberShop::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Barberías';

    protected static ?string $modelLabel = 'Barbería';

    protected static ?string $pluralModelLabel = 'Barberías';

    protected static ?int $navigationSort = 0;

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
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('owner')) {
            return $query->where('owner_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información pública')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->helperText('Se usa en: /barberia/{slug}')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                TextInput::make('city')
                                    ->label('Ciudad')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),
                            ]),

                        Textarea::make('address')
                            ->label('Dirección')
                            ->rows(2)
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Imágenes')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('barber-shops/logos')
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->nullable()
                            ->columnSpan(1),

                        FileUpload::make('cover_image')
                            ->label('Imagen de portada')
                            ->disk('public')
                            ->directory('barber-shops/covers')
                            ->image()
                            ->imageEditor()
                            ->nullable()
                            ->columnSpan(1),
                    ]),

                Section::make('Datos bancarios (para pagos QR)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Banco')
                                    ->placeholder('Ej: Pichincha, Guayaquil')
                                    ->maxLength(100),

                                TextInput::make('bank_account')
                                    ->label('Número de cuenta')
                                    ->maxLength(50),

                                TextInput::make('bank_account_owner')
                                    ->label('Titular')
                                    ->maxLength(255),
                            ]),

                        FileUpload::make('bank_qr_image')
                            ->label('Código QR para pagos')
                            ->helperText('Sube una imagen de tu QR de banco')
                            ->disk('public')
                            ->directory('barber-shops/qr')
                            ->image()
                            ->imageEditor()
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Configuración')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->height(48)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn (string $state): string => "/barberia/{$state}")
                    ->copyable()
                    ->copyMessage('URL copiada'),

                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Teléfono'),

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
            'index' => ListBarberShops::route('/'),
            'create' => CreateBarberShop::route('/create'),
            'edit' => EditBarberShop::route('/{record}/edit'),
        ];
    }
}
