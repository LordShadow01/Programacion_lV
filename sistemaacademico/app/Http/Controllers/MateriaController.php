<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $materias = \App\Models\Materia::with('docente')
            ->when($search, function($query, $search) {
                return $query->where('nombre_materia', 'like', "%{$search}%")
                             ->orWhere('codigo_materia', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->get();
            
        $docentes = \App\Models\Docente::orderBy('nombre')->get();
        return view('materias.index', compact('materias', 'docentes', 'search'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_materia' => 'required|string|max:10|unique:materias',
            'nombre_materia' => 'required|string|max:100',
            'uv' => 'required|integer|min:1',
            'id_docente' => 'nullable|exists:docentes,id'
        ]);

        \App\Models\Materia::create($request->all());

        return redirect()->route('materias.index')->with('success', 'Materia registrada correctamente.');
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
        $materia = \App\Models\Materia::findOrFail($id);

        $request->validate([
            'codigo_materia' => 'required|string|max:10|unique:materias,codigo_materia,'.$id,
            'nombre_materia' => 'required|string|max:100',
            'uv' => 'required|integer|min:1',
            'id_docente' => 'nullable|exists:docentes,id'
        ]);

        $materia->update($request->all());

        return redirect()->route('materias.index')->with('success', 'Materia actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $materia = \App\Models\Materia::findOrFail($id);
        $materia->delete();

        return redirect()->route('materias.index')->with('success', 'Materia eliminada correctamente.');
    }
}
