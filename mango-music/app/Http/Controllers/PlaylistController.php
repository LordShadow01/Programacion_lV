<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return response()->json($playlists);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'caratula' => 'nullable|string'
        ]);

        $playlist = Playlist::create([
            'user_id' => Auth::id(),
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'caratula' => $request->caratula,
            'canciones' => []
        ]);

        return response()->json($playlist, 201);
    }

    public function update(Request $request, $id)
    {
        $playlist = Playlist::where('_id', $id)->where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'titulo' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'caratula' => 'nullable|string'
        ]);

        $playlist->update($request->only(['titulo', 'descripcion', 'caratula']));

        return response()->json($playlist);
    }

    public function destroy($id)
    {
        $playlist = Playlist::where('_id', $id)->where('user_id', Auth::id())->firstOrFail();
        $playlist->delete();

        return response()->json(['message' => 'Playlist eliminada']);
    }

    public function addSong(Request $request, $id)
    {
        $playlist = Playlist::where('_id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        $request->validate([
            'cancion_id' => 'required|string'
        ]);

        $canciones = $playlist->canciones ?? [];
        if (!in_array($request->cancion_id, $canciones)) {
            $canciones[] = $request->cancion_id;
            $playlist->canciones = $canciones;
            $playlist->save();
        }

        return response()->json($playlist);
    }

    public function removeSong($id, $songId)
    {
        // Asegurar que la playlist exista y pertenezca al usuario
        $playlist = Playlist::where('_id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Obtenemos las canciones. Eloquent las decodifica automáticamente a un array PHP gracias a $casts
        $canciones = $playlist->canciones ?? [];
        
        // Caso de respaldo por si el valor recuperado es una cadena JSON cruda
        if (is_string($canciones)) {
            $canciones = json_decode($canciones, true) ?? [];
        }

        // Filtramos para remover el ID de la canción (soportando tanto string como ObjectId en PHP)
        $canciones = array_values(array_filter($canciones, function($cId) use ($songId) {
            return (string)$cId !== (string)$songId;
        }));

        // Guardamos la lista actualizada mediante Eloquent para que maneje la persistencia correctamente
        $playlist->canciones = $canciones;
        $playlist->save();

        // Retornamos la respuesta JSON de éxito solicitada
        return response()->json(['status' => 'success'], 200);
    }

    public function getWithSongs($id)
    {
        $playlist = Playlist::where('_id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        $cancionesIds = $playlist->canciones ?? [];
        $canciones = Cancion::whereIn('_id', $cancionesIds)->get();

        return response()->json([
            'playlist' => $playlist,
            'canciones' => $canciones
        ]);
    }

    public function agregarCancion(Request $request)
    {
        $request->validate([
            'playlist_id' => 'required|string',
            'song_id' => 'required|string'
        ]);

        $playlist = Playlist::where('_id', $request->playlist_id)->where('user_id', Auth::id())->firstOrFail();
        
        $canciones = $playlist->canciones ?? [];
        if (!in_array($request->song_id, $canciones)) {
            $canciones[] = $request->song_id;
            $playlist->canciones = $canciones;
            $playlist->save();
        }

        return response()->json([
            'status' => 'success',
            'playlist' => $playlist
        ]);
    }
}
