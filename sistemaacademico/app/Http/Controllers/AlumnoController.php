<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $alumnos = \App\Models\Alumno::query()
            ->when($search, function($query, $search) {
                return $query->where('nombre', 'like', "%{$search}%")
                             ->orWhere('apellido', 'like', "%{$search}%")
                             ->orWhere('codigo', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('alumnos.index', compact('alumnos', 'search'));
    }

    public function create()
    {
        // Not needed for modal approach
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:alumnos',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'nullable|email|unique:alumnos',
            'telefono' => 'nullable|string|max:9',
            'direccion' => 'nullable|string|max:255',
            'departamento' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        \App\Models\Alumno::create($request->all());

        return redirect()->route('alumnos.index')->with('success', 'Alumno registrado correctamente.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        // Not needed for modal approach
    }

    public function update(Request $request, string $id)
    {
        $alumno = \App\Models\Alumno::findOrFail($id);

        $request->validate([
            'codigo' => 'required|string|max:20|unique:alumnos,codigo,'.$id,
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'nullable|email|unique:alumnos,email,'.$id,
            'telefono' => 'nullable|string|max:9',
            'direccion' => 'nullable|string|max:255',
            'departamento' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $alumno->update($request->all());

        return redirect()->route('alumnos.index')->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $alumno = \App\Models\Alumno::findOrFail($id);
        $alumno->delete();

        return redirect()->route('alumnos.index')->with('success', 'Alumno eliminado correctamente.');
    }
}
