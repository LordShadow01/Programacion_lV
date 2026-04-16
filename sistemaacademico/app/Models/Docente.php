<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $fillable = [
        'codigo', 'nombre', 'apellido', 'email', 'telefono', 'especialidad'
    ];

    public function materias()
    {
        return $this->hasMany(Materia::class, 'id_docente');
    }
}
