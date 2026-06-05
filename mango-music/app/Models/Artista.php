<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Artista extends Model
{
    protected $fillable = [
        'nombre',
        'biografia',
        'foto',
        'genero_musical'
    ];
}
