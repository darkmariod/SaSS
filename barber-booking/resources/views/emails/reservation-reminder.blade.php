@component('mail::message')
# Hola, {{ $customer }}

Te recordamos tu cita de **mañana** en {{ $shop->name }}.

| Detalle | Info |
|---|---|
| **Servicio** | {{ $service }} |
@if ($barber)
| **Barbero** | {{ $barber }} |
@endif
| **Fecha** | {{ $date }} |
| **Hora** | {{ $time }} |
@if ($shop->address)
| **Dirección** | {{ $shop->address }} |
@endif

Si no vas a poder venir, avisanos con tiempo así liberamos el horario para otra persona.

@if ($shop->phone)
{{ $shop->phone }}
@endif

Te esperamos,<br>
{{ $shop->name }}
@endcomponent
