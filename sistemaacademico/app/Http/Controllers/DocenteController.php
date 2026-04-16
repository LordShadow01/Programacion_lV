<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $docentes = \App\Models\Docente::query()
            ->when($search, function($query, $search) {
                return $query->where('nombre', 'like', "%{$search}%")
                             ->orWhere('apellido', 'like', "%{$search}%")
                             ->orWhere('codigo', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('docentes.index', compact('docentes', 'search'));
    }

    public function create()
    {
        // Not needed for modal approach
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:docentes',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'nullable|email|unique:docentes',
            'telefono' => 'nullable|string|max:9',
            'especialidad' => 'nullable|string|max:150',
        ]);

        \App\Models\Docente::create($request->all());

        return redirect()->route('docentes.index')->with('success', 'Docente registrado correctamente.');
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
        $docente = \App\Models\Docente::findOrFail($id);

        $request->validate([
            'codigo' => 'required|string|max:20|unique:docentes,codigo,'.$id,
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'nullable|email|unique:docentes,email,'.$id,
            'telefono' => 'nullable|string|max:9',
            'especialidad' => 'nullable|string|max:150',
        ]);

        $docente->update($request->all());

        return redirect()->route('docentes.index')->with('success', 'Docente actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $docente = \App\Models\Docente::findOrFail($id);
        $docente->delete();

        return redirect()->route('docentes.index')->with('success', 'Docente eliminado correctamente.');
    }
}
