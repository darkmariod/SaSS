<x-filament::page>
    <x-filament::card>
        <div id="calendar-container">
            <div id="calendar"></div>
        </div>
    </x-filament::card>

    {{-- Detail Modal --}}
    <div
        x-data="{ open: false, event: null }"
        x-on:keydown.escape.window="open = false"
        x-cloak
    >
        {{-- Modal backdrop --}}
        <div
            x-show="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            x-transition.opacity
        >
            {{-- Modal content --}}
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-800"
                x-on:click.stop
            >
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100" x-text="event?.title"></h3>
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                        x-on:click="open = false"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 space-y-3 text-sm text-zinc-600 dark:text-zinc-400" x-show="event?.extendedProps">
                    <template x-if="event?.extendedProps?.customer_name">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-zinc-500 dark:text-zinc-300 w-20">Cliente:</span>
                            <span x-text="event.extendedProps.customer_name"></span>
                        </div>
                    </template>
                    <template x-if="event?.extendedProps?.customer_phone">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-zinc-500 dark:text-zinc-300 w-20">Teléfono:</span>
                            <span x-text="event.extendedProps.customer_phone"></span>
                        </div>
                    </template>
                    <template x-if="event?.extendedProps?.barber">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-zinc-500 dark:text-zinc-300 w-20">Barbero:</span>
                            <span x-text="event.extendedProps.barber"></span>
                        </div>
                    </template>
                    <template x-if="event?.extendedProps?.service">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-zinc-500 dark:text-zinc-300 w-20">Servicio:</span>
                            <span x-text="event.extendedProps.service"></span>
                        </div>
                    </template>
                    <template x-if="event?.extendedProps?.total">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-zinc-500 dark:text-zinc-300 w-20">Total:</span>
                            <span x-text="'$' + event.extendedProps.total.toFixed(2)"></span>
                        </div>
                    </template>
                    <template x-if="event?.extendedProps?.status">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-zinc-500 dark:text-zinc-300 w-20">Estado:</span>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="{
                                    'bg-green-100 text-green-700': event.extendedProps.status === 'confirmada',
                                    'bg-amber-100 text-amber-700': event.extendedProps.status === 'pendiente',
                                    'bg-indigo-100 text-indigo-700': event.extendedProps.status === 'completada',
                                    'bg-red-100 text-red-700': event.extendedProps.status === 'cancelada',
                                }"
                                x-text="event.extendedProps.status"
                            ></span>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex justify-end">
                    <a
                        :href="`/admin/reservations/${event?.extendedProps?.id ?? ''}`"
                        class="rounded-lg bg-zinc-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Ver detalle
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'es',
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                height: 'auto',
                firstDay: 1, // Monday
                events: {
                    url: '/api/calendar/events',
                    method: 'GET',
                    failure: function () {
                        console.error('Error loading calendar events');
                    }
                },
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    // Dispatch to Alpine modal
                    const modal = document.querySelector('[x-data]');
                    if (modal && modal.__x) {
                        modal.__x.$data.event = info.event;
                        modal.__x.$data.open = true;
                    }
                },
                loading: function (isLoading) {
                    if (isLoading) {
                        document.getElementById('calendar').classList.add('opacity-50');
                    } else {
                        document.getElementById('calendar').classList.remove('opacity-50');
                    }
                }
            });

            calendar.render();
        });
    </script>
    @endpush

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet" />
    <style>
        #calendar {
            min-height: 500px;
        }
        .fc {
            font-size: 0.9rem;
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 700;
        }
        .fc .fc-button-primary {
            background-color: #18181b;
            border-color: #18181b;
        }
        .fc .fc-button-primary:hover {
            background-color: #27272a;
            border-color: #27272a;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #18181b;
            border-color: #18181b;
        }
        .fc .fc-daygrid-day.fc-day-today {
            background-color: #fef3c7;
        }
        .dark .fc {
            color: #e4e4e7;
        }
        .dark .fc .fc-daygrid-day.fc-day-today {
            background-color: #1c1917;
        }
        .dark .fc .fc-col-header-cell {
            background: #18181b;
            color: #e4e4e7;
        }
        .dark .fc .fc-daygrid-day-frame {
            background: #27272a;
        }
        .dark .fc .fc-scrollgrid {
            border-color: #3f3f46;
        }
        .dark .fc .fc-daygrid-day.fc-day-other .fc-daygrid-day-top {
            opacity: 0.3;
        }
        .dark .fc .fc-timegrid-slot {
            background: #27272a;
        }
        .dark .fc .fc-timegrid-axis {
            background: #18181b;
        }
    </style>
    @endpush
</x-filament::page>
