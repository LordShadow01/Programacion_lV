<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MonetizacionController extends Controller
{
    public function getBalance()
    {
        /** @var User $user */
        $user = Auth::user();

        // Calcular saldo disponible real: total acumulado de transacciones recibidas menos retiros realizados
        $saldoTotal = Transaccion::where('id_artista', $user->id)
            ->where('status', 'COMPLETED')
            ->sum('monto');

        $retiros = Transaccion::where('user_id', $user->id)
            ->where('tipo', 'retiro')
            ->where('estado', 'completado')
            ->sum('monto');

        $saldoReal = (float)($saldoTotal - $retiros);

        $user->saldo = $saldoReal;
        $user->balance = $saldoReal;
        $user->save();

        return response()->json([
            'saldo' => $saldoReal
        ]);
    }

    public function retirar(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:1',
            'metodo' => 'required|string',
            'detalles' => 'nullable|array'
        ]);

        /** @var User $user */
        $user = Auth::user();
        $monto = (float)$request->monto;

        // Validar saldo suficiente
        $saldoTotal = Transaccion::where('id_artista', $user->id)
            ->where('status', 'COMPLETED')
            ->sum('monto');

        $retiros = Transaccion::where('user_id', $user->id)
            ->where('tipo', 'retiro')
            ->where('estado', 'completado')
            ->sum('monto');

        $saldoReal = (float)($saldoTotal - $retiros);

        if ($saldoReal < $monto) {
            return response()->json([
                'error' => 'Saldo insuficiente para realizar este retiro.'
            ], 422);
        }

        // Registrar la transacción/retiro en la colección 'transacciones'
        Transaccion::create([
            'user_id' => $user->id,
            'monto' => $monto,
            'metodo' => $request->metodo,
            'tipo' => 'retiro',
            'estado' => 'completado',
            'detalles' => $request->detalles ?? []
        ]);

        // Recalcular saldo y guardarlo
        $nuevoSaldo = (float)($saldoReal - $monto);
        $user->saldo = $nuevoSaldo;
        $user->balance = $nuevoSaldo;
        $user->save();

        return response()->json([
            'message' => 'Retiro procesado con éxito.',
            'nuevo_saldo' => $nuevoSaldo
        ], 200);
    }

    public function getArtistaMonetizacion(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Obtener transacciones completadas donde el id_artista sea el del usuario autenticado
        $transacciones = Transaccion::where('id_artista', $user->id)
            ->where('status', 'COMPLETED')
            ->orderBy('fecha', 'desc')
            ->get();

        // Clasificar transacciones por monto: 4.99 es suscripción, cualquier otro monto es donación
        $suscripciones = 0;
        $donaciones = 0;

        foreach ($transacciones as $t) {
            $monto = (float)$t->monto;
            if (abs($monto - 4.99) < 0.001) {
                $suscripciones += $monto;
            } else {
                $donaciones += $monto;
            }
        }

        $saldoTotal = $suscripciones + $donaciones;

        // Formatear transacciones para el listado
        $historial = $transacciones->map(function ($t) {
            // Buscar remitente
            $remitenteId = $t->id_remitente ?? $t->user_id;
            $remitente = $remitenteId ? User::find($remitenteId) : null;
            
            $monto = (float)$t->monto;
            $esSuscripcion = abs($monto - 4.99) < 0.001;

            $fechaFormateada = null;
            if (isset($t->fecha)) {
                if (is_object($t->fecha) && get_class($t->fecha) === 'MongoDB\BSON\UTCDateTime') {
                    $fechaFormateada = Carbon::createFromTimestampMs($t->fecha->toDateTime()->getTimestamp() * 1000)->format('d/m/Y H:i');
                } elseif ($t->fecha instanceof \DateTimeInterface) {
                    $fechaFormateada = Carbon::instance($t->fecha)->format('d/m/Y H:i');
                } else {
                    $fechaFormateada = Carbon::parse((string)$t->fecha)->format('d/m/Y H:i');
                }
            }

            return [
                'id' => $t->id,
                'fecha' => $fechaFormateada,
                'remitente' => $remitente ? $remitente->nombre : 'Oyente Anónimo',
                'tipo' => $esSuscripcion ? 'Suscripción' : 'Donación',
                'monto' => $monto
            ];
        });

        // Calcular saldo disponible real: total acumulado de transacciones recibidas menos retiros realizados
        $retiros = Transaccion::where('user_id', $user->id)
            ->where('tipo', 'retiro')
            ->where('estado', 'completado')
            ->sum('monto');

        $saldoReal = (float)($saldoTotal - $retiros);

        $user->saldo = $saldoReal;
        $user->balance = $saldoReal;
        $user->save();

        return response()->json([
            'saldo_disponible' => $saldoReal,
            'saldo_total' => $saldoTotal,
            'suscripciones' => $suscripciones,
            'donaciones' => $donaciones,
            'transacciones' => $historial
        ]);
    }
}
