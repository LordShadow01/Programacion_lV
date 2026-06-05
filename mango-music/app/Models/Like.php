<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['usuario_id', 'cancion_id'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cancion()
    {
        return $this->belongsTo(Cancion::class, 'cancion_id');
    }
}
