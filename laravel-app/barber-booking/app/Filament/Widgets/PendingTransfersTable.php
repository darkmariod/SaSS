<?php

namespace App\Filament\Widgets;

use App\Models\BarberShop;
use App\Models\Transfer;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PendingTransfersTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Comprobantes pendientes de revisión';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTransfersQuery())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Subido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('reservation.customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->placeholder('—'),

                TextColumn::make('reservation.customer_phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('reservation.service.name')
                    ->label('Servicio')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('reservation.consultant.name')
                    ->label('Barbero')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->placeholder('—')
                    ->searchable(),

                ImageColumn::make('receipt_image_path')
                    ->label('Comprobante')
                    ->disk('public')
                    ->height(48)
                    ->square(),
            ])
            ->recordActions([
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
                    ->modalDescription('Esto marcará el pago como pagado y confirmará la reserva.')
                    ->modalSubmitActionLabel('Sí, confirmar')
                    ->action(function (Transfer $record): void {
                        $record->confirm(auth()->id());
                    }),

                Action::make('rejectTransfer')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar transferencia')
                    ->modalDescription('Esto rechazará el comprobante y cancelará la reserva asociada.')
                    ->modalSubmitActionLabel('Sí, rechazar')
                    ->action(function (Transfer $record): void {
                        $record->reject(auth()->id());
                    }),
            ]);
    }

    private function getTransfersQuery(): Builder
    {
        return Transfer::query()
            ->with([
                'barberShop',
                'reservation',
                'reservation.service',
                'reservation.consultant',
            ])
            ->whereIn('barber_shop_id', $this->getVisibleShopIds())
            ->where('status', 'pending');
    }

    private function getVisibleShopIds(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        if ($user->hasRole('owner')) {
            return BarberShop::query()
                ->where('owner_id', $user->id)
                ->pluck('id');
        }

        return BarberShop::query()->pluck('id');
    }
}
