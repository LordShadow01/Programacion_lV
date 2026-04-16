<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumnos';

    protected $fillable = [
        'codigo', 'nombre', 'apellido', 'email', 'telefono', 
        'direccion', 'departamento', 'fecha_nacimiento'
    ];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_alumno');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_alumno');
    }
}
