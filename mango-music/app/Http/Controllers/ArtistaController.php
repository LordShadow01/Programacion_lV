<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Artista;

class ArtistaController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\User::where('rol', 'artista')->get());
    }

    public function show($id)
    {
        \Illuminate\Support\Facades\Log::info("=== FETCHING ARTIST PROFILE ===");
        \Illuminate\Support\Facades\Log::info("Recibido ID en controlador: " . $id);

        $artista = \App\Models\User::where('rol', 'artista')->find($id);

        if (!$artista) {
            \Illuminate\Support\Facades\Log::warning("Artista no encontrado en BD con ID: " . $id);
            return response()->json(['message' => 'Artista no encontrado'], 404);
        }

        // Search songs by user_id
        $query = \App\Models\Cancion::where('user_id', $id);

        try {
            // Fallback: If it's a valid 24-character hex string, also search for MongoDB ObjectId
            if (strlen($id) === 24 && ctype_xdigit($id)) {
                $objectId = new \MongoDB\BSON\ObjectId($id);
                $query->orWhere('user_id', $objectId);
                \Illuminate\Support\Facades\Log::info("Buscando canciones con user_id string '$id' OR ObjectId");
            } else {
                \Illuminate\Support\Facades\Log::info("Buscando canciones con user_id string '$id'");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error parseando ObjectId: " . $e->getMessage());
        }

        $canciones = $query->get();

        \Illuminate\Support\Facades\Log::info("Canciones encontradas para el artista: " . $canciones->count());

        return response()->json([
            'artista' => $artista,
            'canciones' => $canciones
        ]);
    }
}
