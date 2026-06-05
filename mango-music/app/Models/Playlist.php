<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Playlist extends Model
{
    protected $table = 'playlists';

    protected $fillable = [
        'user_id',
        'titulo',
        'descripcion',
        'caratula',
        'canciones'
    ];

    protected $casts = [
        'canciones' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
