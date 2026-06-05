<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Evento;

class EventoController extends Controller
{
    public function index()
    {
        return response()->json(Evento::orderBy('fecha', 'asc')->paginate(20));
    }

    public function userEvents(Request $request, $userId)
    {
        return response()->json(Evento::where('user_id', $userId)->orderBy('fecha', 'asc')->paginate(20));
    }

    public function store(Request $request)
    {
        // TODOS los roles pueden crear eventos (oyente, artista, productor, entidad)

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'lugar' => 'required|string|max:255',
            'fecha' => 'required|date|after_or_equal:today', // Permitir hoy o futuro
            'hora' => 'required|string|max:10',              // Acepta HH:MM o HH:MM:SS
            'es_gratis' => 'required|boolean',
            'cantante_invitado' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string',                   // URL relativa de Storage
        ]);

        // Asignar user_id server-side, no desde cliente (BUG #6)
        $data['user_id'] = $request->user()->id;

        $evento = Evento::create($data);

        return response()->json($evento, 201);
    }

    public function update(Request $request, $id)
    {
        $evento = Evento::findOrFail($id);
        
        // Validar que solo el propietario pueda editar (BUG #2)
        if ($evento->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para editar este evento'
            ], 403);
        }
        
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'lugar' => 'required|string|max:255',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|string|max:10',
            'es_gratis' => 'required|boolean',
            'cantante_invitado' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string',
        ]);

        $evento->update($data);

        return response()->json($evento);
    }

    public function destroy(Request $request, $id)
    {
        $evento = Evento::findOrFail($id);
        
        // Validar que solo el propietario pueda eliminar (BUG #2)
        if ($evento->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar este evento'
            ], 403);
        }

        $evento->delete();
        return response()->json(['message' => 'Evento eliminado']);
    }

    public function buscar(Request $request)
    {
        $termino = $request->q;

        $query = Evento::query();

        if ($termino) {
            $query->where(function($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                  ->orWhere('lugar', 'like', '%' . $termino . '%');
            });
        }

        $results = $query->orderBy('fecha', 'asc')->paginate(20);

        \Illuminate\Support\Facades\Log::info("Busqueda eventos - Termino: '$termino', Encontrados: " . count($results->items()));

        return response()->json($results);
    }
}
