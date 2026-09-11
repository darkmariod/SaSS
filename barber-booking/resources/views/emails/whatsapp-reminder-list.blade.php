@component('mail::message')
# Recordatorios para mañana

Estas citas de **{{ $shop->name }}** no dejaron correo. Tocá cada botón y el mensaje sale listo por WhatsApp.

@foreach ($enlaces as $e)
**{{ $e['hora'] }}** · {{ $e['cliente'] }} · {{ $e['servicio'] }}

@component('mail::button', ['url' => $e['url']])
Enviar recordatorio a {{ $e['cliente'] }}
@endcomponent

@endforeach
@endcomponent
