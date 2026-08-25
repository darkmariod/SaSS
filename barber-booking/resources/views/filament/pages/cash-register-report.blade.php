<x-filament::page>
    @php
        $totals = $this->totals();
        $registers = $this->registers();
        $shops = $this->availableShops();
        $money = fn ($v) => '$ ' . number_format((float) $v, 2);
        $dif = (float) $totals['difference'];
    @endphp

    {{--
        Los estilos van aquí y no en clases de Tailwind porque el panel de
        Filament NO carga el CSS de la aplicación: usa el suyo. Las utilidades
        escritas en esta vista se descartaban al compilar y el informe se veía
        como una pila de tarjetas sin cuadrícula ni tabla.
    --}}
    <style>
        .cr-filtros { display: grid; gap: 1rem; grid-template-columns: 1fr; }
        @media (min-width: 640px) { .cr-filtros { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .cr-filtros { grid-template-columns: repeat(4, 1fr); } }

        .cr-campo label { display: block; font-size: .8125rem; font-weight: 600; margin-bottom: .35rem; opacity: .75; }
        .cr-campo select, .cr-campo input {
            width: 100%; border-radius: .5rem; font-size: .875rem;
            border: 1px solid rgba(128, 122, 112, .35);
            background: transparent; color: inherit; padding: .5rem .65rem;
        }

        .cr-periodo { font-size: .875rem; opacity: .8; align-self: end; }
        .cr-periodo strong { display: block; font-size: 1rem; opacity: 1; }

        .cr-grupo { margin-top: 1.5rem; }
        .cr-grupo h3 {
            font-size: .7rem; font-weight: 700; letter-spacing: .12em;
            text-transform: uppercase; opacity: .55; margin: 0 0 .6rem;
        }

        .cr-cifras { display: grid; gap: .75rem; grid-template-columns: repeat(2, 1fr); }
        @media (min-width: 900px) { .cr-cifras { grid-template-columns: repeat(5, 1fr); } }
        .cr-cifras.cr-tres { grid-template-columns: repeat(1, 1fr); }
        @media (min-width: 640px) { .cr-cifras.cr-tres { grid-template-columns: repeat(3, 1fr); } }

        .cr-dato {
            border: 1px solid rgba(128, 122, 112, .22);
            border-radius: .6rem; padding: .8rem .9rem;
        }
        .cr-dato span { display: block; font-size: .78rem; opacity: .65; }
        .cr-dato strong {
            display: block; margin-top: .25rem; font-size: 1.4rem;
            font-weight: 700; font-variant-numeric: tabular-nums;
        }
        .cr-dato.cr-fuerte { border-width: 2px; }
        .cr-verde  { color: #15803d; } .cr-rojo { color: #b91c1c; }
        @media (prefers-color-scheme: dark) { .cr-verde { color: #4ade80; } .cr-rojo { color: #f87171; } }

        .cr-scroll { overflow-x: auto; }
        .cr-tabla { width: 100%; border-collapse: collapse; font-size: .875rem; }
        .cr-tabla th, .cr-tabla td { padding: .55rem .75rem; white-space: nowrap; }
        .cr-tabla thead th {
            text-align: left; font-weight: 600; font-size: .75rem;
            text-transform: uppercase; letter-spacing: .04em; opacity: .6;
            border-bottom: 1px solid rgba(128, 122, 112, .3);
        }
        .cr-tabla td.cr-num, .cr-tabla th.cr-num { text-align: right; font-variant-numeric: tabular-nums; }
        .cr-tabla tbody tr { border-bottom: 1px solid rgba(128, 122, 112, .15); }
        .cr-tabla tfoot td { border-top: 2px solid rgba(128, 122, 112, .4); font-weight: 700; }
        .cr-vacio { text-align: center; padding: 2rem 0; opacity: .55; }
        .cr-pill { border-radius: 999px; padding: .15rem .6rem; font-size: .72rem; font-weight: 700; border: 1px solid currentColor; }
        .cr-aviso {
            margin-top: 1rem; border-radius: .5rem; padding: .6rem .9rem; font-size: .875rem;
            border: 1px solid rgba(180, 130, 20, .45); color: #92400e;
        }
        @media (prefers-color-scheme: dark) { .cr-aviso { color: #fcd34d; } }
    </style>

    {{-- ── Filtros ── --}}
    <x-filament::card>
        <div class="cr-filtros">
            <div class="cr-campo">
                <label>Período</label>
                <select wire:model.live="periodType">
                    <option value="week">Semanal</option>
                    <option value="month">Mensual</option>
                </select>
            </div>

            <div class="cr-campo">
                <label>{{ $periodType === 'week' ? 'Día dentro de la semana' : 'Día dentro del mes' }}</label>
                <input type="date" wire:model.live="anchorDate" />
            </div>

            @if (count($shops) > 1)
                <div class="cr-campo">
                    <label>Barbería</label>
                    <select wire:model.live="shopId">
                        <option value="">Todas</option>
                        @foreach ($shops as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="cr-periodo">
                <strong>{{ $this->periodLabel() }}</strong>
                {{ $totals['count'] }} caja(s) · {{ $totals['closed'] }} cerrada(s) · {{ $totals['open'] }} abierta(s)
            </div>
        </div>

        @if ($totals['open'] > 0)
            <div class="cr-aviso">
                Hay {{ $totals['open'] }} caja(s) sin cerrar en este período: los totales todavía pueden cambiar.
            </div>
        @endif
    </x-filament::card>

    {{-- ── Movimiento del período ── --}}
    <div class="cr-grupo">
        <h3>Movimiento del período</h3>
        <div class="cr-cifras">
            <div class="cr-dato"><span>Apertura</span><strong>{{ $money($totals['opening']) }}</strong></div>
            <div class="cr-dato"><span>Cobrado en efectivo</span><strong class="cr-verde">{{ $money($totals['cash_income']) }}</strong></div>
            <div class="cr-dato"><span>Por transferencia</span><strong class="cr-verde">{{ $money($totals['transfer_income']) }}</strong></div>
            <div class="cr-dato"><span>Otros ingresos</span><strong class="cr-verde">{{ $money($totals['manual_income']) }}</strong></div>
            <div class="cr-dato"><span>Gastos</span><strong class="cr-rojo">{{ $money($totals['expense']) }}</strong></div>
        </div>
    </div>

    {{-- ── Cuadre del efectivo ──
         Va aparte porque responde otra pregunta: no cuánto entró, sino si el
         dinero del cajón coincide. Las transferencias no cuentan acá. --}}
    <div class="cr-grupo">
        <h3>Cuadre del efectivo — las transferencias no entran al cajón</h3>
        <div class="cr-cifras cr-tres">
            <div class="cr-dato cr-fuerte"><span>Debe haber en el cajón</span><strong>{{ $money($totals['system']) }}</strong></div>
            <div class="cr-dato cr-fuerte"><span>Contado</span><strong>{{ $money($totals['real']) }}</strong></div>
            <div class="cr-dato cr-fuerte">
                <span>{{ $dif == 0.0 ? 'Cuadra' : ($dif > 0 ? 'Sobra' : 'Falta') }}</span>
                <strong class="{{ $dif == 0.0 ? 'cr-verde' : 'cr-rojo' }}">{{ $money($totals['difference']) }}</strong>
            </div>
        </div>
    </div>

    {{-- ── Detalle por día ── --}}
    <x-filament::card>
        <h3 style="font-weight:700;margin:0 0 .75rem;">Detalle diario</h3>

        <div class="cr-scroll">
            <table class="cr-tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        @if (count($shops) > 1)<th>Barbería</th>@endif
                        <th class="cr-num">Apertura</th>
                        <th class="cr-num">Efectivo</th>
                        <th class="cr-num">Transfer.</th>
                        <th class="cr-num">Gastos</th>
                        <th class="cr-num">Debe haber</th>
                        <th class="cr-num">Contado</th>
                        <th class="cr-num">Sobra / falta</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registers as $reg)
                        <tr>
                            <td>{{ $reg->date->format('d/m/Y') }}</td>
                            @if (count($shops) > 1)<td>{{ $reg->barberShop?->name }}</td>@endif
                            <td class="cr-num">{{ $money($reg->opening_amount) }}</td>
                            <td class="cr-num">{{ $money($reg->cash_income_amount) }}</td>
                            <td class="cr-num">{{ $money($reg->transfer_income_amount) }}</td>
                            <td class="cr-num cr-rojo">{{ $money($reg->expense_amount) }}</td>
                            <td class="cr-num"><strong>{{ $money($reg->system_amount) }}</strong></td>
                            <td class="cr-num">{{ $reg->real_amount !== null ? $money($reg->real_amount) : '—' }}</td>
                            <td class="cr-num {{ (float) $reg->difference_amount == 0.0 ? '' : 'cr-rojo' }}">
                                {{ $money($reg->difference_amount) }}
                            </td>
                            <td><span class="cr-pill">{{ $reg->status === 'closed' ? 'Cerrada' : 'Abierta' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="cr-vacio">
                                Todavía no se abrió ninguna caja en este período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($registers->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="{{ count($shops) > 1 ? 2 : 1 }}">TOTAL</td>
                            <td class="cr-num">{{ $money($totals['opening']) }}</td>
                            <td class="cr-num">{{ $money($totals['cash_income']) }}</td>
                            <td class="cr-num">{{ $money($totals['transfer_income']) }}</td>
                            <td class="cr-num">{{ $money($totals['expense']) }}</td>
                            <td class="cr-num">{{ $money($totals['system']) }}</td>
                            <td class="cr-num">{{ $money($totals['real']) }}</td>
                            <td class="cr-num">{{ $money($totals['difference']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-filament::card>
</x-filament::page>
