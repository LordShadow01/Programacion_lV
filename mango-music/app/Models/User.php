<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        'nombre_artistico',
        'nombre_productor',
        'nombre_entidad',
        'foto',
        'biografia',
        'fecha_nacimiento',
        'genero_musical',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function seguidores()
    {
        return $this->hasMany(Seguidor::class, 'artista_id');
    }

    public function siguiendo()
    {
        return $this->hasMany(Seguidor::class, 'seguidor_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'usuario_id');
    }

    public function canciones()
    {
        return $this->hasMany(Cancion::class, 'user_id');
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'user_id');
    }
}
