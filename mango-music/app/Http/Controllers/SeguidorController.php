<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seguidor;
use App\Models\User;

class SeguidorController extends Controller
{
    public function follow(Request $request)
    {
        $request->validate([
            'seguidor_id' => 'required|exists:usuarios,id',
            'artista_id' => 'required|exists:usuarios,id',
        ]);

        if ($request->seguidor_id == $request->artista_id) {
            return response()->json(['message' => 'No puedes seguirte a ti mismo'], 400);
        }

        // BUG #28 - No permitir seguir a oyentes, solo a artistas/productores/entidades
        $artista = User::findOrFail($request->artista_id);
        if ($artista->rol === 'oyente') {
            return response()->json(['message' => 'No puedes seguir a usuarios regulares, solo a artistas, productores o entidades'], 400);
        }

        $seguidor = Seguidor::firstOrCreate([
            'seguidor_id' => $request->seguidor_id,
            'artista_id' => $request->artista_id,
        ]);

        return response()->json($seguidor, 201);
    }

    public function unfollow(Request $request)
    {
        $request->validate([
            'seguidor_id' => 'required|exists:usuarios,id',
            'artista_id' => 'required|exists:usuarios,id',
        ]);

        Seguidor::where('seguidor_id', $request->seguidor_id)
            ->where('artista_id', $request->artista_id)
            ->delete();

        return response()->json(['message' => 'Has dejado de seguir al artista']);
    }

    public function status($seguidorId, $artistaId)
    {
        $exists = Seguidor::where('seguidor_id', $seguidorId)
            ->where('artista_id', $artistaId)
            ->exists();
        return response()->json(['following' => $exists]);
    }

    public function count($artistaId)
    {
        $count = Seguidor::where('artista_id', $artistaId)->count();
        return response()->json(['count' => $count]);
    }
}
