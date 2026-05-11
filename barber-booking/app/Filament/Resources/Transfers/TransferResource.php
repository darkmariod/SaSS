<?php

namespace App\Filament\Resources\Transfers;

use App\Filament\Resources\Transfers\Pages\ListTransfers;
use App\Filament\Resources\Transfers\Pages\ViewTransfer;
use App\Filament\Resources\Transfers\Schemas\TransferForm;
use App\Models\Transfer;
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

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transferencias';

    protected static ?string $modelLabel = 'Transferencia';

    protected static ?string $pluralModelLabel = 'Transferencias';

    protected static ?int $navigationSort = 6;

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
                'reservation',
                'reservation.service',
                'reservation.addonService',
                'reservation.consultant',
                'confirmedBy',
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
        return TransferForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la transferencia')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('barberShop.name')
                                    ->label('Barbería')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('reservation.customer_name')
                                    ->label('Cliente')
                                    ->placeholder('Sin reserva asociada'),

                                TextEntry::make('reservation.customer_phone')
                                    ->label('Teléfono')
                                    ->placeholder('Sin teléfono'),

                                TextEntry::make('reservation.service.name')
                                    ->label('Servicio')
                                    ->placeholder('Sin servicio'),

                                TextEntry::make('reservation.addonService.name')
                                    ->label('Extra')
                                    ->placeholder('Sin extra'),

                                TextEntry::make('reservation.consultant.name')
                                    ->label('Barbero')
                                    ->placeholder('Sin barbero'),

                                TextEntry::make('reference')
                                    ->label('Referencia')
                                    ->placeholder('Sin referencia'),

                                TextEntry::make('amount')
                                    ->label('Monto')
                                    ->money('USD'),

                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'Pendiente',
                                        'confirmed' => 'Confirmada',
                                        'rejected' => 'Rechazada',
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('confirmedBy.name')
                                    ->label('Revisado por')
                                    ->placeholder('Sin revisar'),

                                TextEntry::make('confirmed_at')
                                    ->label('Fecha de revisión')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Sin revisar'),

                                TextEntry::make('created_at')
                                    ->label('Subida el')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),

                Section::make('Comprobante')
                    ->schema([
                        TextEntry::make('receipt_image_path')
                            ->label('Archivo')
                            ->placeholder('Sin comprobante'),

                        TextEntry::make('receipt_url')
                            ->label('Ver comprobante')
                            ->state(function (Transfer $record): string {
                                if (! $record->receipt_image_path) {
                                    return 'Sin comprobante';
                                }

                                return asset('storage/' . $record->receipt_image_path);
                            })
                            ->url(function (Transfer $record): ?string {
                                if (! $record->receipt_image_path) {
                                    return null;
                                }

                                return asset('storage/' . $record->receipt_image_path);
                            })
                            ->openUrlInNewTab(),
                    ]),

                Section::make('Notas')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('Sin notas'),
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

                TextColumn::make('barberShop.name')
                    ->label('Barbería')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reservation.customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->placeholder('—'),

                TextColumn::make('reservation.consultant.name')
                    ->label('Barbero')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('reservation.service.name')
                    ->label('Servicio')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'rejected' => 'Rechazada',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        'default' => 'gray',
                    })
                    ->sortable(),

                ImageColumn::make('receipt_image_path')
                    ->label('Comprobante')
                    ->disk('public')
                    ->height(48)
                    ->square(),

                TextColumn::make('confirmedBy.name')
                    ->label('Revisado por')
                    ->placeholder('—'),

                TextColumn::make('confirmed_at')
                    ->label('Revisado el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'rejected' => 'Rechazada',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver'),

                Action::make('openReceipt')
                    ->label('Ver comprobante')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->url(function (Transfer $record): ?string {
                        if (! $record->receipt_image_path) {
                            return null;
                        }

                        return asset('storage/' . $record->receipt_image_path);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Transfer $record): bool => filled($record->receipt_image_path)),

                Action::make('confirmTransfer')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar transferencia')
                    ->modalDescription('Esto marcará la transferencia como confirmada, el pago como pagado y la reserva como confirmada.')
                    ->modalSubmitActionLabel('Sí, confirmar')
                    ->visible(function (Transfer $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->status === 'pending';
                    })
                    ->action(function (Transfer $record): void {
                        $record->confirm(auth()->id());
                    }),

                Action::make('rejectTransfer')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar transferencia')
                    ->modalDescription('Esto marcará la transferencia como rechazada y cancelará la reserva asociada.')
                    ->modalSubmitActionLabel('Sí, rechazar')
                    ->visible(function (Transfer $record): bool {
                        return auth()->user()?->hasAnyRole(['owner', 'admin'])
                            && $record->status === 'pending';
                    })
                    ->action(function (Transfer $record): void {
                        $record->reject(auth()->id());
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
            'index' => ListTransfers::route('/'),
            'create' => \App\Filament\Resources\Transfers\Pages\CreateTransfer::route('/create'),
            'view' => ViewTransfer::route('/{record}'),
            'edit' => \App\Filament\Resources\Transfers\Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
