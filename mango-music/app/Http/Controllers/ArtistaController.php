<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use Illuminate\Http\Request;

class ArtistaController extends Controller
{
    public function index(Request $request)
    {
        $query = Artista::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('genero', 'LIKE', "%{$search}%")
                  ->orWhere('ciudad', 'LIKE', "%{$search}%")
                  ->orWhere('correo', 'LIKE', "%{$search}%")
                  ->orWhere('telefono', 'LIKE', "%{$search}%");
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:255',
            'ciudad' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
        ]);
        
        $artista = Artista::create($validated);
        return response()->json(['message' => 'Artista registrado exitosamente.', 'data' => $artista]);
    }

    public function update(Request $request, $id)
    {
        $artista = Artista::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:255',
            'ciudad' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
        ]);

        $artista->update($validated);
        return response()->json(['message' => 'Datos del artista actualizados.', 'data' => $artista]);
    }

    public function destroy($id)
    {
        $artista = Artista::findOrFail($id);
        $artista->delete();
        return response()->json(['message' => 'Artista eliminado del sistema.']);
    }
}
