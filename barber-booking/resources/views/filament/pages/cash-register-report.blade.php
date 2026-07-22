<x-filament::page>
    @php
        $totals = $this->totals();
        $registers = $this->registers();
        $shops = $this->availableShops();
        $money = fn ($v) => '$ ' . number_format((float) $v, 2);
    @endphp

    {{-- ── Filtros ── --}}
    <x-filament::card>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Período</label>
                <select
                    wire:model.live="periodType"
                    class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 text-sm"
                >
                    <option value="week">Semanal</option>
                    <option value="month">Mensual</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    {{ $periodType === 'week' ? 'Día dentro de la semana' : 'Día dentro del mes' }}
                </label>
                <input
                    type="date"
                    wire:model.live="anchorDate"
                    class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 text-sm"
                />
            </div>

            @if (count($shops) > 1)
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Barbería</label>
                    <select
                        wire:model.live="shopId"
                        class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 text-sm"
                    >
                        <option value="">Todas</option>
                        @foreach ($shops as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex items-end">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                    <span class="block font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->periodLabel() }}</span>
                    {{ $totals['count'] }} caja(s) · {{ $totals['closed'] }} cerrada(s) · {{ $totals['open'] }} abierta(s)
                </div>
            </div>
        </div>

        @if ($totals['open'] > 0)
            <div class="mt-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-2 text-sm text-amber-800 dark:text-amber-200">
                ⚠️ Hay {{ $totals['open'] }} caja(s) todavía abierta(s) en este período. El total no es definitivo hasta cerrarlas.
            </div>
        @endif
    </x-filament::card>

    {{-- ── Totales del período ── --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Apertura', $totals['opening'], 'text-zinc-600 dark:text-zinc-300'],
            ['Efectivo', $totals['cash_income'], 'text-emerald-600 dark:text-emerald-400'],
            ['Transferencias', $totals['transfer_income'], 'text-emerald-600 dark:text-emerald-400'],
            ['Ingresos manuales', $totals['manual_income'], 'text-emerald-600 dark:text-emerald-400'],
            ['Egresos', $totals['expense'], 'text-red-600 dark:text-red-400'],
            ['Total sistema', $totals['system'], 'text-indigo-600 dark:text-indigo-400 font-bold'],
            ['Total real contado', $totals['real'], 'text-zinc-800 dark:text-zinc-100'],
            ['Diferencia', $totals['difference'], ((float) $totals['difference'] === 0.0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400') . ' font-bold'],
        ] as [$label, $value, $color])
            <x-filament::card>
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                <div class="mt-1 text-2xl {{ $color }}">{{ $money($value) }}</div>
            </x-filament::card>
        @endforeach
    </div>

    {{-- ── Detalle por día ── --}}
    <x-filament::card>
        <h3 class="text-base font-semibold text-zinc-800 dark:text-zinc-100 mb-3">Detalle diario del período</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left text-zinc-500 dark:text-zinc-400">
                        <th class="py-2 pr-4 font-medium">Fecha</th>
                        @if (count($shops) > 1)
                            <th class="py-2 pr-4 font-medium">Barbería</th>
                        @endif
                        <th class="py-2 pr-4 font-medium text-right">Apertura</th>
                        <th class="py-2 pr-4 font-medium text-right">Efectivo</th>
                        <th class="py-2 pr-4 font-medium text-right">Transfer.</th>
                        <th class="py-2 pr-4 font-medium text-right">Egresos</th>
                        <th class="py-2 pr-4 font-medium text-right">Sistema</th>
                        <th class="py-2 pr-4 font-medium text-right">Real</th>
                        <th class="py-2 pr-4 font-medium text-right">Diferencia</th>
                        <th class="py-2 pr-4 font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registers as $reg)
                        <tr class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-2 pr-4 text-zinc-800 dark:text-zinc-200">{{ $reg->date->format('d/m/Y') }}</td>
                            @if (count($shops) > 1)
                                <td class="py-2 pr-4 text-zinc-600 dark:text-zinc-400">{{ $reg->barberShop?->name }}</td>
                            @endif
                            <td class="py-2 pr-4 text-right">{{ $money($reg->opening_amount) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $money($reg->cash_income_amount) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $money($reg->transfer_income_amount) }}</td>
                            <td class="py-2 pr-4 text-right text-red-600 dark:text-red-400">{{ $money($reg->expense_amount) }}</td>
                            <td class="py-2 pr-4 text-right font-semibold">{{ $money($reg->system_amount) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $reg->real_amount !== null ? $money($reg->real_amount) : '—' }}</td>
                            <td class="py-2 pr-4 text-right {{ (float) $reg->difference_amount === 0.0 ? 'text-zinc-500' : 'text-red-600 dark:text-red-400' }}">
                                {{ $money($reg->difference_amount) }}
                            </td>
                            <td class="py-2 pr-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $reg->status === 'closed' ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                    {{ $reg->status === 'closed' ? 'Cerrada' : 'Abierta' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-zinc-400 dark:text-zinc-500">
                                No hay cajas registradas en este período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($registers->isNotEmpty())
                    <tfoot>
                        <tr class="border-t-2 border-zinc-300 dark:border-zinc-600 font-bold text-zinc-800 dark:text-zinc-100">
                            <td class="py-2 pr-4" colspan="{{ count($shops) > 1 ? 2 : 1 }}">TOTAL</td>
                            <td class="py-2 pr-4 text-right">{{ $money($totals['opening']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $money($totals['cash_income']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $money($totals['transfer_income']) }}</td>
                            <td class="py-2 pr-4 text-right text-red-600 dark:text-red-400">{{ $money($totals['expense']) }}</td>
                            <td class="py-2 pr-4 text-right text-indigo-600 dark:text-indigo-400">{{ $money($totals['system']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $money($totals['real']) }}</td>
                            <td class="py-2 pr-4 text-right {{ (float) $totals['difference'] === 0.0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $money($totals['difference']) }}</td>
                            <td class="py-2 pr-4"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-filament::card>
</x-filament::page>
