<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Datos de cobro de la plataforma
    |--------------------------------------------------------------------------
    |
    | Cuenta a la que las barberías transfieren la suscripción. Se muestran en la
    | pantalla de renovación. Configuralos con variables de entorno en producción.
    |
    */
    'bank' => env('PLATFORM_BANK', 'Banco Pichincha'),
    'account' => env('PLATFORM_ACCOUNT', '0000000000'),
    'account_owner' => env('PLATFORM_ACCOUNT_OWNER', 'BookingEc'),
    'account_type' => env('PLATFORM_ACCOUNT_TYPE', 'Ahorros'),
    'document_id' => env('PLATFORM_DOCUMENT_ID', ''),
    'whatsapp' => env('PLATFORM_WHATSAPP', ''),
];
