<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cancion extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'artista_id', 'duracion', 'archivo_audio'];

    public function artista()
    {
        return $this->belongsTo(Artista::class);
    }
}
