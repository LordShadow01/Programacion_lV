<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Seguidor extends Model
{
    protected $table = 'seguidores';
    protected $fillable = ['seguidor_id', 'artista_id'];

    public function seguidor()
    {
        return $this->belongsTo(User::class, 'seguidor_id');
    }

    public function artista()
    {
        return $this->belongsTo(User::class, 'artista_id');
    }
}
