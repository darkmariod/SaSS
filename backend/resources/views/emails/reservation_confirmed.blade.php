<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Reserva</title>
</head>
<body style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding:20px;">

    <div style="max-width:600px; margin:auto; background:white; padding:30px; border-radius:8px;">

        <h2 style="color:#2c3e50;">Reserva Confirmada ✅</h2>

        <p>Hola <strong>{{ $user->nombres }} {{ $user->apellidos }}</strong>,</p>

        <p>Tu reserva ha sido confirmada correctamente.</p>

        <hr>

        <p><strong>Fecha:</strong> {{ $paypalOrder->reservation_date }}</p>

        <p><strong>Horas reservadas:</strong></p>
        <ul>
            @foreach($paypalOrder->hours as $hour)
                <li>{{ $hour }}</li>
            @endforeach
        </ul>

        <p><strong>Total pagado:</strong> {{ $paypalOrder->total_amount }} USD</p>

        <hr>

        <p style="color:#7f8c8d;">
            Gracias por confiar en nosotros.<br>
            Este correo es automático, no responder.
        </p>

    </div>

</body>
</html>
