<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayPal Client ID
    |--------------------------------------------------------------------------
    | Clave pública del SDK de PayPal. Se usa en el frontend para cargar
    | los Smart Payment Buttons y en el backend para autenticarse.
    |
    */
    'client_id' => env('PAYPAL_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | PayPal Secret (Client Secret)
    |--------------------------------------------------------------------------
    | Clave privada del SDK de PayPal. Usada EXCLUSIVAMENTE en el backend
    | para generar el Access Token de OAuth 2.0. NUNCA exponer en el frontend.
    |
    */
    'secret' => env('PAYPAL_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | PayPal Mode
    |--------------------------------------------------------------------------
    | 'sandbox' para pruebas, 'live' para producción.
    |
    */
    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | PayPal API URLs
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'sandbox' => 'https://api-m.sandbox.paypal.com',
        'live'    => 'https://api-m.paypal.com',
    ],
];
