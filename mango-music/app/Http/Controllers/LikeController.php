<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Cancion;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'cancion_id' => 'required|exists:canciones,id',
        ]);

        $like = Like::where('usuario_id', $request->usuario_id)
            ->where('cancion_id', $request->cancion_id)
            ->first();

        if ($like) {
            $like->delete();
            return response()->json(['liked' => false]);
        } else {
            Like::create([
                'usuario_id' => $request->usuario_id,
                'cancion_id' => $request->cancion_id,
            ]);
            return response()->json(['liked' => true]);
        }
    }

    public function countForArtist($artistaId)
    {
        // BUG #30 - Optimizar query N+1 usando whereHas/subquery
        $count = Like::whereHas('cancion', function($query) use ($artistaId) {
            $query->where('user_id', $artistaId);
        })->count();
        return response()->json(['count' => $count]);
    }
}
