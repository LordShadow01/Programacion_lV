<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $inscripciones = \App\Models\Inscripcion::with(['alumno', 'materia'])
            ->when($search, function($query, $search) {
                return $query->whereHas('alumno', function($q) use ($search) {
                                 $q->where('nombre', 'like', "%{$search}%")
                                   ->orWhere('apellido', 'like', "%{$search}%");
                             })
                             ->orWhereHas('materia', function($q) use ($search) {
                                 $q->where('nombre_materia', 'like', "%{$search}%");
                             });
            })
            ->orderBy('id', 'desc')
            ->get();
            
        $alumnos = \App\Models\Alumno::orderBy('nombre')->get();
        $materias = \App\Models\Materia::orderBy('nombre_materia')->get();
        
        return view('inscripciones.index', compact('inscripciones', 'alumnos', 'materias', 'search'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_alumno' => 'required|exists:alumnos,id',
            'id_materia' => 'required|exists:materias,id',
            'ciclo' => 'required|in:Ciclo I,Ciclo II',
            'anio' => 'required|date_format:Y',
            'nota_final' => 'numeric|min:0|max:10',
            'estado_materia' => 'required|in:Cursando,Aprobada,Reprobada,Retirada'
        ]);

        \App\Models\Inscripcion::create($request->all());

        return redirect()->route('inscripciones.index')->with('success', 'Inscripción registrada correctamente.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $inscripcion = \App\Models\Inscripcion::findOrFail($id);

        $request->validate([
            'id_alumno' => 'required|exists:alumnos,id',
            'id_materia' => 'required|exists:materias,id',
            'ciclo' => 'required|in:Ciclo I,Ciclo II',
            'anio' => 'required|date_format:Y',
            'nota_final' => 'numeric|min:0|max:10',
            'estado_materia' => 'required|in:Cursando,Aprobada,Reprobada,Retirada'
        ]);

        $inscripcion->update($request->all());

        return redirect()->route('inscripciones.index')->with('success', 'Inscripción actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $inscripcion = \App\Models\Inscripcion::findOrFail($id);
        $inscripcion->delete();

        return redirect()->route('inscripciones.index')->with('success', 'Inscripción eliminada correctamente.');
    }
}
