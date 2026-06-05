<?php

namespace App\Http\Controllers;

use App\Models\SolicitudColaboracion;
use App\Models\User;
use App\Mail\AceptacionColaboracion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProductorSolicitudController extends Controller
{
    /**
     * Traer todas las solicitudes creadas por el productor autenticado.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        // Verificar rol
        if (Auth::user()->rol !== 'productor') {
            return response()->json(['error' => 'No autorizado. Se requiere rol de Productor.'], 403);
        }

        $solicitudes = SolicitudColaboracion::with('productor')
            ->where('productor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($solicitudes);
    }

    /**
     * Validar y crear una nueva solicitud de colaboración.
     */
    public function store(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        if (Auth::user()->rol !== 'productor') {
            return response()->json(['error' => 'No autorizado. Se requiere rol de Productor.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'genero_musical' => 'required|string|max:100',
            'fecha_limite' => 'nullable|date|after:today',
            'limite_postulantes' => 'nullable|integer|min:1',
            'imagen_cabecera' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Errores de validacion:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imgPath = null;
        if ($request->hasFile('imagen_cabecera')) {
            $file = $request->file('imagen_cabecera');
            $path = $file->store('convocatorias', 'public');
            $imgPath = '/storage/' . $path;
        }

        $solicitud = SolicitudColaboracion::create([
            'productor_id' => Auth::id(),
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'genero_musical' => $request->genero_musical,
            'fecha_limite' => $request->fecha_limite ? \Carbon\Carbon::parse($request->fecha_limite)->startOfDay() : null,
            'limite_postulantes' => $request->limite_postulantes ? (int)$request->limite_postulantes : null,
            'imagen_cabecera' => $imgPath,
            'estado' => 'activa',
            'postulantes' => []
        ]);

        return response()->json($solicitud, 201);
    }

    /**
     * Listar los artistas que han aplicado a una solicitud específica, trayendo sus nombres y perfiles.
     */
    public function getPostulantes(string $id): \Illuminate\Http\JsonResponse
    {
        if (Auth::user()->rol !== 'productor') {
            return response()->json(['error' => 'No autorizado. Se requiere rol de Productor.'], 403);
        }

        $solicitud = SolicitudColaboracion::where('_id', $id)
            ->where('productor_id', Auth::id())
            ->firstOrFail();

        $postulantesData = $solicitud->postulantes ?? [];

        // Resolver la información del artista para cada postulación
        $result = [];
        foreach ($postulantesData as $p) {
            $artistaId = $p['artista_id'] ?? null;
            if ($artistaId) {
                $artista = User::find($artistaId);
                if ($artista) {
                    $result[] = [
                        'artista_id' => $artistaId,
                        'nombre' => $artista->nombre,
                        'nombre_artistico' => $artista->nombre_artistico ?? $artista->nombre,
                        'email' => $artista->email,
                        'foto' => $artista->foto,
                        'biografia' => $artista->biografia,
                        'fecha_postulacion' => $p['fecha_postulacion'] ?? null,
                        'mensaje_motivacional' => $p['mensaje_motivacional'] ?? '',
                        'estado_postulacion' => $p['estado_postulacion'] ?? 'pendiente'
                    ];
                }
            }
        }

        return response()->json($result);
    }

    /**
     * Permitir al productor cerrar o pausar la oferta ('activa' / 'cerrada').
     */
    public function cambiarEstado(\Illuminate\Http\Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        if (Auth::user()->rol !== 'productor') {
            return response()->json(['error' => 'No autorizado. Se requiere rol de Productor.'], 403);
        }

        $request->validate([
            'estado' => 'required|string|in:activa,cerrada',
        ]);

        $solicitud = SolicitudColaboracion::where('_id', $id)
            ->where('productor_id', Auth::id())
            ->firstOrFail();

        $solicitud->estado = $request->estado;
        $solicitud->save();

        // Recargar desde MongoDB para garantizar que la respuesta JSON
        // refleje el estado actualizado (el update() no recarga el modelo automáticamente)
        $solicitud->refresh();

        return response()->json($solicitud);
    }

    /**
     * Cambiar el estado de postulación de un artista.
     */
    public function cambiarEstadoPostulante(\Illuminate\Http\Request $request, string $id, string $artistaId): \Illuminate\Http\JsonResponse
    {
        if (Auth::user()->rol !== 'productor') {
            return response()->json(['error' => 'No autorizado. Se requiere rol de Productor.'], 403);
        }

        $request->validate([
            'estado_postulacion' => 'required|string|in:pendiente,aceptado,rechazado',
        ]);

        $solicitud = SolicitudColaboracion::where('_id', $id)
            ->where('productor_id', Auth::id())
            ->firstOrFail();

        $postulantes = $solicitud->postulantes ?? [];
        $encontrado = false;

        foreach ($postulantes as &$p) {
            if ((string)($p['artista_id'] ?? '') === (string)$artistaId) {
                $p['estado_postulacion'] = $request->estado_postulacion;
                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            return response()->json(['error' => 'No se encontró la postulación del artista especificado.'], 404);
        }

        $solicitud->postulantes = $postulantes;
        $solicitud->save();

        // ── Enviar correo de notificación al artista si fue aceptado ──
        if ($request->estado_postulacion === 'aceptado') {
            try {
                $artista = User::find($artistaId);
                if ($artista && $artista->email) {
                    $solicitudData = [
                        'titulo'         => $solicitud->titulo,
                        'genero_musical' => $solicitud->genero_musical,
                        'descripcion'    => $solicitud->descripcion,
                    ];
                    $postulanteData = [
                        'nombre'           => $artista->nombre,
                        'nombre_artistico' => $artista->nombre_artistico ?? $artista->nombre,
                        'email'            => $artista->email,
                    ];
                    Mail::to($artista->email)
                        ->send(new AceptacionColaboracion($solicitudData, $postulanteData));
                }
            } catch (\Exception $e) {
                // El fallo en el correo NO debe romper la respuesta principal
                Log::warning('Error enviando correo de aceptación: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message'     => 'Estado de postulación actualizado.',
            'postulantes' => $postulantes
        ]);
    }

    /**
     * Listar todas las solicitudes activas para que los artistas puedan aplicar.
     */
    public function listarTodasActivas(): \Illuminate\Http\JsonResponse
    {
        $solicitudes = SolicitudColaboracion::with('productor')
            ->where('estado', 'activa')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($solicitudes);
    }

    /**
     * Método para que el artista autenticado agregue su postulación a la lista de candidatos de una solicitud.
     */
    public function aplicarASolicitud(\Illuminate\Http\Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        // Validar que sea artista
        if (Auth::user()->rol !== 'artista') {
            return response()->json(['error' => 'No autorizado. Solo los usuarios con rol Artista pueden postularse.'], 403);
        }

        $request->validate([
            'mensaje_motivacional' => 'required|string',
        ]);

        $solicitud = SolicitudColaboracion::where('_id', $id)->firstOrFail();

        if ($solicitud->estado !== 'activa') {
            return response()->json(['error' => 'Esta solicitud de colaboración ya no se encuentra activa.'], 400);
        }

        // 1. Validar Fecha Límite
        if ($solicitud->fecha_limite) {
            $limite = $solicitud->fecha_limite instanceof \Carbon\Carbon 
                ? $solicitud->fecha_limite 
                : \Carbon\Carbon::parse($solicitud->fecha_limite);
                
            if (now()->greaterThan($limite)) {
                return response()->json(['error' => 'La fecha límite de postulación para esta convocatoria ya ha expirado.'], 400);
            }
        }

        $postulantes = $solicitud->postulantes ?? [];

        // 2. Validar Límite de Postulantes
        if ($solicitud->limite_postulantes && count($postulantes) >= (int)$solicitud->limite_postulantes) {
            return response()->json(['error' => 'Esta convocatoria ya ha alcanzado el límite máximo de postulantes.'], 400);
        }

        // Validar si ya se postuló
        $artistaId = Auth::id();
        foreach ($postulantes as $p) {
            if ((string)($p['artista_id'] ?? '') === (string)$artistaId) {
                return response()->json(['error' => 'Ya te has postulado a esta colaboración anteriormente.'], 400);
            }
        }

        // Agregar la nueva postulación con estado 'pendiente' por defecto
        $postulantes[] = [
            'artista_id' => $artistaId,
            'fecha_postulacion' => now()->toIso8601String(),
            'mensaje_motivacional' => $request->mensaje_motivacional,
            'estado_postulacion' => 'pendiente'
        ];

        $solicitud->postulantes = $postulantes;
        $solicitud->save();

        return response()->json([
            'message' => 'Postulación registrada exitosamente.',
            'solicitud' => $solicitud
        ]);
    }
}
