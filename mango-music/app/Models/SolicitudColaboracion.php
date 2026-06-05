<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SolicitudColaboracion extends Model
{
    protected $table = 'solicitudes_colaboracion';

    protected $fillable = [
        'productor_id',
        'titulo',
        'descripcion',
        'genero_musical',
        'estado',      // 'activa' o 'cerrada'
        'postulantes',  // array de subdocumentos: [ ['artista_id' => ..., 'fecha_postulacion' => ..., 'mensaje_motivacional' => ..., 'estado_postulacion' => ...] ]
        'fecha_limite',
        'limite_postulantes',
        'imagen_cabecera',
    ];

    protected $casts = [
        'postulantes' => 'array',
        'fecha_limite' => 'datetime',
        'limite_postulantes' => 'integer',
        'imagen_cabecera' => 'string',
    ];

    /**
     * Relación con el creador/productor
     */
    public function productor()
    {
        return $this->belongsTo(User::class, 'productor_id');
    }
}
