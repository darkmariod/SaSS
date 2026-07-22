<?php

namespace App\Filament\Pages;

use App\Models\BarberShop;
use App\Models\CashRegister;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CashRegisterReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Cierres semanal / mensual';

    protected static ?string $title = 'Cierres de caja — semanal y mensual';

    protected static ?string $slug = 'cash-report';

    protected static ?int $navigationSort = 8;

    /** @var 'week'|'month' */
    public string $periodType = 'month';

    /** Any date inside the target week/month. */
    public string $anchorDate = '';

    /** Selected shop, or null for "all accessible shops". */
    public ?int $shopId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['owner', 'admin']) ?? false;
    }

    public function mount(): void
    {
        $this->anchorDate = now()->toDateString();
    }

    public function getView(): string
    {
        return 'filament.pages.cash-register-report';
    }

    /**
     * Shops the current user may report on: an owner only sees the shops they
     * own; an admin sees every shop.
     *
     * @return array<int, string>
     */
    public function availableShops(): array
    {
        $user = auth()->user();

        $query = BarberShop::query()->orderBy('name');

        if ($user?->hasRole('owner')) {
            $query->where('owner_id', $user->id);
        }

        return $query->pluck('name', 'id')->all();
    }

    /**
     * Resolve the [start, end] boundaries of the selected period.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function periodRange(): array
    {
        $anchor = Carbon::parse($this->anchorDate ?: now()->toDateString());

        if ($this->periodType === 'week') {
            return [
                $anchor->copy()->startOfWeek(CarbonInterface::MONDAY),
                $anchor->copy()->endOfWeek(CarbonInterface::SUNDAY),
            ];
        }

        return [$anchor->copy()->startOfMonth(), $anchor->copy()->endOfMonth()];
    }

    /**
     * Daily cash registers that fall inside the selected period and are visible
     * to the current user. Closed registers are immutable snapshots; open ones
     * are included but flagged so the operator knows the figure is not final.
     */
    public function registers(): Collection
    {
        [$start, $end] = $this->periodRange();

        $shopIds = array_keys($this->availableShops());

        if ($shopIds === []) {
            return collect();
        }

        return CashRegister::query()
            ->with(['barberShop', 'closedBy'])
            ->whereIn('barber_shop_id', $shopIds)
            ->when($this->shopId, fn ($q) => $q->where('barber_shop_id', $this->shopId))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();
    }

    /**
     * Aggregate the daily registers into the period totals shown at the top of
     * the report.
     *
     * @return array<string, float|int>
     */
    public function totals(): array
    {
        $registers = $this->registers();

        return [
            'opening' => round((float) $registers->sum('opening_amount'), 2),
            'cash_income' => round((float) $registers->sum('cash_income_amount'), 2),
            'transfer_income' => round((float) $registers->sum('transfer_income_amount'), 2),
            'manual_income' => round((float) $registers->sum('manual_income_amount'), 2),
            'expense' => round((float) $registers->sum('expense_amount'), 2),
            'system' => round((float) $registers->sum('system_amount'), 2),
            'real' => round((float) $registers->sum('real_amount'), 2),
            'difference' => round((float) $registers->sum('difference_amount'), 2),
            'count' => $registers->count(),
            'closed' => $registers->where('status', 'closed')->count(),
            'open' => $registers->where('status', 'open')->count(),
        ];
    }

    public function periodLabel(): string
    {
        [$start, $end] = $this->periodRange();

        if ($this->periodType === 'week') {
            return 'Semana del ' . $start->translatedFormat('d M') . ' al ' . $end->translatedFormat('d M Y');
        }

        return ucfirst($start->translatedFormat('F Y'));
    }
}
