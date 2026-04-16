<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materias';

    protected $fillable = [
        'codigo_materia', 'nombre_materia', 'uv', 'id_docente'
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_materia');
    }
}
