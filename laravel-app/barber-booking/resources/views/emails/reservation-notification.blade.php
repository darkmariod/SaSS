@component('mail::message')
    {{-- Header --}}
    @slot('subject')
        Nueva Reserva - {{ $service->name }}
    @endslot

    {{-- Saludo --}}
    # Hola, {{ $shop->name }}

    {{-- Detalles --}}
    **Se ha realizado una nueva reserva:**\n
    - **Cliente:** {{ $customer }}
    - **Teléfono:** {{ $phone }}
    - **Servicio:** {{ $service->name }}
    - **Fecha:** {{ $date }}
    - **Hora:** {{ $time }}
    - **Total:** ${{ number_format($total, 2) }}

    {{-- Acción --}}
    @component('mail::button', ['url' => url("/admin/reservations/{$reservation->id}")])
        Ver Reserva en el Panel
    @endcomponent

    {{-- Footer --}}
    @component('mail::footer')
        BarberBooking EC - Sistema de Reservas
    @endcomponent
@endcomponent
