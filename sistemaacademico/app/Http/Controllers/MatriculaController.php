<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MatriculaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $matriculas = \App\Models\Matricula::with('alumno')
            ->when($search, function($query, $search) {
                return $query->whereHas('alumno', function($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                          ->orWhere('apellido', 'like', "%{$search}%")
                          ->orWhere('codigo', 'like', "%{$search}%");
                    });
            })
            ->orderBy('id', 'desc')
            ->get();
            
        $alumnos = \App\Models\Alumno::orderBy('nombre')->get();
        return view('matriculas.index', compact('matriculas', 'alumnos', 'search'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_alumno' => 'required|exists:alumnos,id',
            'anio_academico' => 'required|date_format:Y',
            'ciclo' => 'required|in:Ciclo I,Ciclo II,Ciclo Extraordinario',
            'fecha_pago' => 'nullable|date',
            'estado' => 'required|in:Pendiente,Pagado,Retirado'
        ]);

        \App\Models\Matricula::create($request->all());

        return redirect()->route('matriculas.index')->with('success', 'Matrícula registrada correctamente.');
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
        $matricula = \App\Models\Matricula::findOrFail($id);

        $request->validate([
            'id_alumno' => 'required|exists:alumnos,id',
            'anio_academico' => 'required|date_format:Y',
            'ciclo' => 'required|in:Ciclo I,Ciclo II,Ciclo Extraordinario',
            'fecha_pago' => 'nullable|date',
            'estado' => 'required|in:Pendiente,Pagado,Retirado'
        ]);

        $matricula->update($request->all());

        return redirect()->route('matriculas.index')->with('success', 'Matrícula actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $matricula = \App\Models\Matricula::findOrFail($id);
        $matricula->delete();

        return redirect()->route('matriculas.index')->with('success', 'Matrícula eliminada.');
    }
}
