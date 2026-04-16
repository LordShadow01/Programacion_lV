<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matriculas';

    protected $fillable = [
        'id_alumno', 'anio_academico', 'ciclo', 'fecha_pago', 'estado'
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }
}
