<?php

namespace App\Filament\Widgets;

use App\Models\BarberShop;
use App\Models\CashRegister;
use App\Models\Reservation;
use App\Models\Transfer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class BusinessStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getListeners(): array
    {
        return [
            'statsRefresh' => ['$refresh'],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $shopIds = $this->getVisibleShopIds();

        $todayReservations = Reservation::query()
            ->whereIn('barber_shop_id', $shopIds)
            ->whereDate('reservation_date', today())
            ->count();

        $pendingTransfers = Transfer::query()
            ->whereIn('barber_shop_id', $shopIds)
            ->where('status', 'pending')
            ->count();

        $openCashRegisters = CashRegister::query()
            ->whereIn('barber_shop_id', $shopIds)
            ->where('status', 'open')
            ->count();

        $todayConfirmedTransfers = Transfer::query()
            ->whereIn('barber_shop_id', $shopIds)
            ->where('status', 'confirmed')
            ->whereDate('confirmed_at', today())
            ->sum('amount');

        $todayCashPayments = Reservation::query()
            ->whereIn('barber_shop_id', $shopIds)
            ->where('payment_status', 'pagado')
            ->whereNull('transfer_id')
            ->whereDate('reservation_date', today())
            ->sum('total_amount');

        $todayIncome = (float) $todayConfirmedTransfers + (float) $todayCashPayments;

        return [
            Stat::make('Reservas de hoy', $todayReservations)
                ->description('Citas agendadas para hoy')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),

            Stat::make('Comprobantes pendientes', $pendingTransfers)
                ->description('Transferencias por revisar')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($pendingTransfers > 0 ? 'warning' : 'success'),

            Stat::make('Cajas abiertas', $openCashRegisters)
                ->description('Cajas diarias sin cerrar')
                ->descriptionIcon('heroicon-o-calculator')
                ->color($openCashRegisters > 0 ? 'success' : 'gray'),

            Stat::make('Ingresos confirmados hoy', '$ ' . number_format($todayIncome, 2))
                ->description('Transferencias confirmadas + pagos en efectivo')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
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
