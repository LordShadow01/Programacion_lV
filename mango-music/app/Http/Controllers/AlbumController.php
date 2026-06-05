<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller
{
    public function dynamicAlbums()
    {
        $userId = Auth::id();
        
        // Find all likes for the user
        $likes = Like::where('usuario_id', $userId)->get();
        $cancionIds = $likes->pluck('cancion_id')->toArray();
        
        // Get those songs
        $canciones = Cancion::whereIn('_id', $cancionIds)->get();
        
        // Group songs by artist
        $groupedByArtist = $canciones->groupBy('artista');
        
        $albums = [];
        
        foreach ($groupedByArtist as $artist => $songs) {
            if ($songs->count() >= 3) {
                // Determine a cover image (first song's cover)
                $cover = $songs->first()->caratula ?? null;
                
                $albums[] = [
                    'id' => 'album_' . md5($artist . $userId),
                    'titulo' => 'Colección: ' . $artist,
                    'artista' => $artist,
                    'caratula' => $cover,
                    'canciones_count' => $songs->count(),
                    'canciones' => $songs->values()->toArray()
                ];
            }
        }
        
        return response()->json($albums);
    }
}
