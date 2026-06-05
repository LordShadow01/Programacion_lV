<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = [
        'nombre',
        'cantante_invitado',
        'lugar',
        'fecha',
        'hora',
        'es_gratis',
        'precio',
        'descripcion',
        'imagen',
        'user_id'
    ];
}
