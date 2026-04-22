<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CancionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cancion::with('artista');
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('duracion', 'LIKE', "%{$search}%")
                  ->orWhereHas('artista', function($q) use ($search) {
                      $q->where('nombre', 'LIKE', "%{$search}%");
                  });
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'artista_id' => 'required|exists:artistas,id',
            'duracion' => 'required|string|max:50',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480'
        ]);

        $data = $request->only(['titulo', 'artista_id', 'duracion']);
        
        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('canciones', 'public');
            $data['archivo_audio'] = $path;
        }

        $cancion = Cancion::create($data);
        $cancion->load('artista');
        return response()->json(['message' => 'Canción publicada exitosamente.', 'data' => $cancion]);
    }

    public function update(Request $request, $id)
    {
        $cancion = Cancion::findOrFail($id);
        $request->validate([
            'titulo' => 'required|string|max:255',
            'artista_id' => 'required|exists:artistas,id',
            'duracion' => 'required|string|max:50',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:20480'
        ]);

        $data = $request->only(['titulo', 'artista_id', 'duracion']);
        
        if ($request->hasFile('audio')) {
            // Eliminar audio anterior si existe
            if ($cancion->archivo_audio) {
                Storage::disk('public')->delete($cancion->archivo_audio);
            }
            $path = $request->file('audio')->store('canciones', 'public');
            $data['archivo_audio'] = $path;
        }

        $cancion->update($data);
        $cancion->load('artista');
        return response()->json(['message' => 'Datos de la canción actualizados.', 'data' => $cancion]);
    }

    public function destroy($id)
    {
        $cancion = Cancion::findOrFail($id);
        if ($cancion->archivo_audio) {
            Storage::disk('public')->delete($cancion->archivo_audio);
        }
        $cancion->delete();
        return response()->json(['message' => 'Canción eliminada del catálogo.']);
    }
}
