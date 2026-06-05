<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Cancion;

class CancionController extends Controller
{
    public function index()
    {
        // Paginación para mejor rendimiento (BUG #17)
        return response()->json(Cancion::paginate(20));
    }

    public function store(Request $request)
    {
        // Validar que solo artistas puedan publicar (BUG #5)
        if ($request->user()->rol !== 'artista') {
            return response()->json([
                'message' => 'Solo los artistas pueden publicar canciones'
            ], 403);
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'artista' => 'required|string|max:255',
            'genero' => 'required|string',
            'url_audio' => 'required|string', // BUG #11
            'caratula' => 'nullable|string',  // BUG #11
            'duracion' => 'nullable|string', // BUG #11
        ]);

        // Asignar user_id del usuario autenticado, no desde cliente
        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        $cancion = Cancion::create($data);
        return response()->json($cancion, 201);
    }

    public function destroy(Request $request, $id)
    {
        $cancion = Cancion::findOrFail($id);
        
        // Validar que solo el propietario pueda eliminar (BUG #1)
        if ($cancion->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar esta canción'
            ], 403);
        }

        $cancion->delete();
        return response()->json(['message' => 'Cancion eliminada'], 200);
    }

    public function update(Request $request, $id)
    {
        $cancion = Cancion::findOrFail($id);

        if ($cancion->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permiso para editar esta canción'], 403);
        }

        $request->validate([
            'titulo'   => 'sometimes|string|max:255',
            'genero'   => 'sometimes|string',
            'caratula' => 'sometimes|nullable|string',
        ]);

        $cancion->fill($request->only(['titulo', 'genero', 'caratula']))->save();

        return response()->json($cancion, 200);
    }

    public function pin(Request $request, $id)
    {
        $cancion = Cancion::findOrFail($id);

        if ($cancion->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permiso para fijar esta canción'], 403);
        }

        $cancion->is_pinned = !$cancion->is_pinned;
        $cancion->save();

        return response()->json([
            'message'   => $cancion->is_pinned ? 'Canción fijada' : 'Canción desfijada',
            'is_pinned' => $cancion->is_pinned,
        ], 200);
    }

    public function buscar(Request $request)
    {
        // Búsqueda por dos o más campos (titulo, artista, genero)
        $termino = $request->q;
        $genero = $request->genero;

        $query = Cancion::query();

        if ($termino) {
            $query->where(function($q) use ($termino) {
                $q->where('titulo', 'like', '%' . $termino . '%')
                  ->orWhere('artista', 'like', '%' . $termino . '%')
                  ->orWhere('genero', 'like', '%' . $termino . '%');
            });
        }

        if ($genero) {
            $query->where('genero', 'like', $genero);
        }

        $results = $query->paginate(20);

        \Illuminate\Support\Facades\Log::info("Busqueda canciones - Termino: '$termino', Genero: '$genero', Encontrados: " . count($results->items()));

        return response()->json($results); // Paginación
    }

    public function stream(Request $request, $filename)
    {
        $path = storage_path('app/public/audio/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $size = filesize($path);
        $file = fopen($path, 'r');
        
        $headers = [
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
        ];

        if ($request->headers->has('Range')) {
            $range = $request->header('Range');
            $range = str_replace('bytes=', '', $range);
            $range = explode('-', $range);
            $start = (int) $range[0];
            $end = isset($range[1]) && $range[1] !== '' ? (int) $range[1] : $size - 1;

            $length = $end - $start + 1;

            fseek($file, $start);

            $headers['Content-Length'] = $length;
            $headers['Content-Range'] = "bytes $start-$end/$size";

            return response()->stream(function () use ($file, $length) {
                $buffer = 1024 * 8;
                $bytes_sent = 0;
                while (!feof($file) && $bytes_sent < $length) {
                    $to_send = min($buffer, $length - $bytes_sent);
                    echo fread($file, $to_send);
                    flush();
                    $bytes_sent += $to_send;
                }
                fclose($file);
            }, 206, $headers);
        }

        $headers['Content-Length'] = $size;

        return response()->stream(function () use ($file) {
            fpassthru($file);
            fclose($file);
        }, 200, $headers);
    }
}
