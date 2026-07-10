@component('mail::message')
    {{-- Greeting depends on the explicit audience passed by the Mailable, never on auth() --}}
    @if ($isStaff)
        # Hola, {{ $shop->name }}

        Se ha registrado una nueva reserva con los siguientes detalles:
    @else
        # Hola, {{ $customer }}

        ¡Tu reserva quedó registrada! Estos son los detalles:
    @endif

    | Detalle | Info |
    |---|---|
    | **Cliente** | {{ $customer }} |
    | **Teléfono** | {{ $phone }} |
    | **Servicio** | {{ $service->name }} |
    | **Fecha** | {{ $date }} |
    | **Hora** | {{ $time }} |
    | **Total** | ${{ number_format($total, 2) }} |

    {{-- The .ics attachment lets any recipient add this to their calendar --}}
    El archivo `.ics` adjunto podés abrirlo para agregar esta reserva a tu calendario (Google Calendar, Apple Calendar, Outlook).

    {{-- Only staff (owner/barber) get a link into the admin panel --}}
    @if ($isStaff)
        @component('mail::button', ['url' => url("/admin/reservations/{$reservation->id}")])
            Ver reserva en el panel
        @endcomponent
    @endif

    @component('mail::footer')
        Booking Ec - Sistema de Reservas
    @endcomponent
@endcomponent
