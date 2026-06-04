@component('mail::message')
    {{-- Header --}}
    @slot('subject')
        @if ($reservation->customer_email === auth()->user()?->email ?? null)
            Confirmación de Reserva - {{ $service->name }}
        @else
            Nueva Reserva - {{ $service->name }}
        @endif
    @endslot

    {{-- Saludo dinámico --}}
    @php
        $isOwner = ($reservation->customer_email !== null && $reservation->customer_email === (auth()->user()?->email ?? null)) ? false : true;
    @endphp

    @if ($isOwner)
        # Hola, {{ $shop->name }}
    @else
        # Hola, {{ $customer }}
    @endif

    Se ha registrado una reserva con los siguientes detalles:

    | Detalle | Info |
    |---|---|
    | **Cliente** | {{ $customer }} |
    | **Teléfono** | {{ $phone }} |
    | **Servicio** | {{ $service->name }} |
    | **Fecha** | {{ $date }} |
    | **Hora** | {{ $time }} |
    | **Total** | ${{ number_format($total, 2) }} |

    {{-- Incluir archivo .ics adjunto para agregar al calendario --}}
    El archivo `.ics` adjunto podés abrirlo para agregar esta reserva a tu calendario (Google Calendar, Apple Calendar, Outlook).

    @component('mail::button', ['url' => url("/admin/reservations/{$reservation->id}")])
        Ver Reserva en el Panel
    @endcomponent

    @component('mail::footer')
        BarberBooking EC - Sistema de Reservas
    @endcomponent
@endcomponent
