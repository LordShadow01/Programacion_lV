<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Cancion extends Model
{
    protected $table = 'canciones';

    protected $fillable = [
        'titulo',
        'artista',
        'genero',
        'url_audio',
        'caratula',
        'duracion',
        'user_id',
        'reproducciones',
        'is_pinned',
    ];

    protected $casts = [
        'reproducciones' => 'integer',
        'is_pinned'      => 'boolean',
    ];
}
