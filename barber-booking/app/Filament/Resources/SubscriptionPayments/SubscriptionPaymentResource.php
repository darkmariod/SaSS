<?php

namespace App\Filament\Resources\SubscriptionPayments;

use App\Filament\Resources\SubscriptionPayments\Pages\ListSubscriptionPayments;
use App\Models\SubscriptionPayment;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Platform-side review of subscription payments. Only the super_admin (the SaaS
 * operator) can see and confirm these — a confirmed payment activates/extends
 * the tenant's subscription.
 */
class SubscriptionPaymentResource extends Resource
{
    protected static ?string $model = SubscriptionPayment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Pagos de suscripción';

    protected static ?string $modelLabel = 'Pago de suscripción';

    protected static ?string $pluralModelLabel = 'Pagos de suscripción';

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
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
                    ->weight(FontWeight::Bold),

                TextColumn::make('plan.name')
                    ->label('Plan'),

                TextColumn::make('billing_period')
                    ->label('Período')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'annual' ? 'Anual' : 'Mensual')
                    ->color(fn (string $state): string => $state === 'annual' ? 'success' : 'info'),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'rejected' => 'Rechazado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('period_end')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'rejected' => 'Rechazado',
                    ]),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SubscriptionPayment $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar pago')
                    ->modalDescription('Se activará la suscripción de la barbería por el período pagado.')
                    ->action(fn (SubscriptionPayment $record) => $record->confirm(auth()->id())),

                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SubscriptionPayment $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (SubscriptionPayment $record) => $record->reject(auth()->id())),

                Action::make('receipt')
                    ->label('Ver comprobante')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->url(fn (SubscriptionPayment $record): ?string => $record->receipt_url, shouldOpenInNewTab: true)
                    ->visible(fn (SubscriptionPayment $record): bool => (bool) $record->receipt_image_path),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionPayments::route('/'),
        ];
    }
}
