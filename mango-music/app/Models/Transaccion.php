<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Transaccion extends Model
{
    protected $table = 'transacciones';

    protected $fillable = [
        'id_remitente',
        'id_artista',
        'monto',
        'tipo', // 'donacion' o 'suscripcion'
        'paypal_order_id',
        'status',
        'fecha',
        // Retrocompatibilidad
        'user_id',
        'metodo',
        'estado',
        'detalles'
    ];
}
