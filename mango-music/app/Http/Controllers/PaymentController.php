<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaccion;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Obtener las credenciales y el token de acceso de PayPal.
     * Usa config() en lugar de env() para que funcione incluso con caché de configuración.
     */
    private function getPayPalClient()
    {
        $clientId = config('paypal.client_id');
        $secret   = config('paypal.secret');
        $mode     = config('paypal.mode', 'sandbox');
        $baseUrl  = config('paypal.urls.' . $mode, 'https://api-m.sandbox.paypal.com');

        // --- Depuración de credenciales (TEMPORAL) ---
        Log::error('PayPal Config Debug', [
            'exact_client_id' => $clientId,
            'exact_secret'    => $secret,
            'client_id_length' => strlen($clientId ?? ''),
            'secret_length'    => strlen($secret ?? ''),
        ]);

        if (empty($clientId) || empty($secret)) {
            throw new \Exception(
                'Faltan las credenciales PayPal en .env: PAYPAL_CLIENT_ID y PAYPAL_SECRET son requeridas.'
            );
        }

        // Construcción manual del header Basic para garantizar compatibilidad
        $credentials = base64_encode("{$clientId}:{$secret}");

        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => "Basic {$credentials}",
                'Accept'        => 'application/json',
            ])
            ->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        Log::debug('PayPal Token Response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->failed()) {
            $errorBody = $response->json();
            throw new \Exception(
                'Error al obtener el Access Token de PayPal (HTTP ' . $response->status() . '): '
                . ($errorBody['error_description'] ?? $response->body())
            );
        }

        $token = $response->json('access_token');

        if (empty($token)) {
            throw new \Exception('PayPal devolvió una respuesta exitosa pero sin access_token.');
        }

        return [
            'token' => $token,
            'url'   => $baseUrl,
        ];
    }

    /**
     * Crear una orden en PayPal.
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.50',
            'id_artista' => 'required|string',
            'tipo' => 'required|string|in:donacion,suscripcion'
        ]);

        try {
            $client = $this->getPayPalClient();
            
            $response = Http::withToken($client['token'])
                ->post("{$client['url']}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => number_format($request->monto, 2, '.', '')
                            ],
                            'description' => $request->tipo === 'suscripcion' 
                                ? 'Suscripción al Fan Club' 
                                : 'Donación de Apoyo'
                        ]
                    ],
                    'application_context' => [
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action'         => 'PAY_NOW'
                    ]
                ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'No se pudo crear la orden de PayPal',
                    'details' => $response->json()
                ], 500);
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            Log::error('PayPal Create Order Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error en el servidor de pagos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Capturar el pago de PayPal y guardar en MongoDB.
     */
    public function captureOrder(Request $request)
    {
        $request->validate([
            'orderID' => 'required|string',
            'id_artista' => 'required|string',
            'tipo' => 'required|string|in:donacion,suscripcion',
            'monto' => 'required|numeric'
        ]);

        try {
            $client = $this->getPayPalClient();

            $response = Http::withToken($client['token'])
                ->withBody('{}', 'application/json')
                ->post("{$client['url']}/v2/checkout/orders/{$request->orderID}/capture");

            if ($response->failed()) {
                return response()->json([
                    'error' => 'No se pudo procesar la captura de la orden en PayPal',
                    'details' => $response->json()
                ], 500);
            }

            $paypalData = $response->json();

            if (($paypalData['status'] ?? '') === 'COMPLETED') {
                // Registrar la transacción de forma exitosa en MongoDB
                $transaccion = Transaccion::create([
                    'id_remitente' => $request->user()->id,
                    'id_artista' => $request->id_artista,
                    'monto' => (float) $request->monto,
                    'tipo' => $request->tipo,
                    'paypal_order_id' => $request->orderID,
                    'status' => 'COMPLETED',
                    'fecha' => now(),
                    // Compatibilidad
                    'user_id' => $request->user()->id,
                    'metodo' => 'PayPal',
                    'estado' => 'completado',
                    'detalles' => 'Pago procesado vía PayPal API'
                ]);

                $artista = User::find($request->id_artista);
                if ($artista) {
                    $balanceActual = (float) ($artista->balance ?? 0);
                    $artista->balance = $balanceActual + (float) $request->monto;
                    
                    $saldoActual = (float) ($artista->saldo ?? 0);
                    $artista->saldo = $saldoActual + (float) $request->monto;
                    
                    $artista->save();
                }

                return response()->json([
                    'message' => '¡Pago capturado y guardado con éxito!',
                    'transaccion' => $transaccion
                ], 201);
            }

            return response()->json([
                'error' => 'El pago no ha sido completado',
                'status' => $paypalData['status'] ?? 'UNKNOWN'
            ], 400);

        } catch (\Exception $e) {
            Log::error('PayPal Capture Order Error: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'error' => 'Error interno al capturar la orden o guardar en la base de datos',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Obtener el historial y el total acumulado que ha recibido un artista.
     */
    public function getArtistEarnings($id_artista)
    {
        try {
            // Obtener todas las transacciones completadas dirigidas al artista
            $transacciones = Transaccion::where('id_artista', $id_artista)
                ->where('status', 'COMPLETED')
                ->orderBy('fecha', 'desc')
                ->get();

            // Cargar los datos de los usuarios remitentes
            $historial = $transacciones->map(function ($t) {
                $remitente = User::find($t->id_remitente);
                return [
                    'id' => $t->id,
                    'monto' => $t->monto,
                    'tipo' => $t->tipo,
                    'paypal_order_id' => $t->paypal_order_id,
                    'fecha' => $t->fecha,
                    'remitente' => $remitente ? [
                        'nombre' => $remitente->nombre,
                        'email' => $remitente->email,
                        'foto' => $remitente->foto
                    ] : [
                        'nombre' => 'Oyente Anónimo',
                        'email' => 'anonimo@mangomusic.com',
                        'foto' => null
                    ]
                ];
            });

            $totalAcumulado = $transacciones->sum('monto');

            return response()->json([
                'total_acumulado' => $totalAcumulado,
                'transacciones' => $historial
            ]);

        } catch (\Exception $e) {
            Log::error('Get Artist Earnings Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al consultar las ganancias del artista',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
