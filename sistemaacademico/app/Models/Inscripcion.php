<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'inscripcions';

    protected $fillable = [
        'id_alumno', 'id_materia', 'ciclo', 'anio', 'nota_final', 'estado_materia'
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia');
    }
}
