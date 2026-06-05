<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancionController;
use App\Http\Controllers\ArtistaController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\SeguidorController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\PaymentController;

// Public Routes
Route::post('/registro', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password/send', [AuthController::class, 'sendResetCode']);
Route::post('/forgot-password/verify', [AuthController::class, 'verifyResetCode']);
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword']);

Route::get('/stats', function() {
    return response()->json([
        'artistas' => \App\Models\User::where('rol', 'artista')->count(),
        'canciones' => \App\Models\Cancion::count(),
        'oyentes' => \App\Models\User::where('rol', 'oyente')->count(),
    ]);
});

// Public Data Routes
Route::get('/canciones', [CancionController::class, 'index']);
Route::get('/stream/audio/{filename}', [CancionController::class, 'stream']);
Route::get('/buscar/canciones', [CancionController::class, 'buscar']);
Route::get('/artistas', [ArtistaController::class, 'index']);
Route::get('/artistas/{id}', [ArtistaController::class, 'show']);
Route::get('/eventos', [EventoController::class, 'index']);
Route::get('/buscar/eventos', [EventoController::class, 'buscar']);
Route::get('/seguidores/count/{artistaId}', [SeguidorController::class, 'count']);
Route::get('/likes/count/artista/{artistaId}', [LikeController::class, 'countForArtist']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/usuario', [AuthController::class, 'user']);
    Route::post('/usuario/update', [AuthController::class, 'updateProfile']);
    Route::post('/usuario/change-password', [AuthController::class, 'changePassword']);

    // Canciones - Solo ARTISTA puede crear
    Route::post('/canciones', [CancionController::class, 'store']);
    Route::put('/canciones/{id}', [CancionController::class, 'update']);
    Route::delete('/canciones/{id}', [CancionController::class, 'destroy']);
    Route::patch('/canciones/{id}/pin', [CancionController::class, 'pin']);
    
    // Upload - Separados por tipo
    Route::post('/upload/audio', [UploadController::class, 'uploadAudio']);
    Route::post('/upload/image', [UploadController::class, 'uploadImage']);
    Route::post('/upload', [UploadController::class, 'upload']); // Legacy, deprecated



    // Eventos
    Route::get('/eventos/usuario/{userId}', [EventoController::class, 'userEvents']);
    Route::post('/eventos', [EventoController::class, 'store']);
    Route::put('/eventos/{id}', [EventoController::class, 'update']);
    Route::delete('/eventos/{id}', [EventoController::class, 'destroy']);

    // Seguidores
    Route::post('/seguidores/follow', [SeguidorController::class, 'follow']);
    Route::post('/seguidores/unfollow', [SeguidorController::class, 'unfollow']);
    Route::get('/seguidores/status/{seguidorId}/{artistaId}', [SeguidorController::class, 'status']);

    // Likes
    Route::post('/likes/toggle', [LikeController::class, 'toggle']);

    // Playlists
    Route::get('/playlists', [PlaylistController::class, 'index']);
    Route::post('/playlists', [PlaylistController::class, 'store']);
    Route::get('/playlists/{id}', [PlaylistController::class, 'getWithSongs']);
    Route::put('/playlists/{id}', [PlaylistController::class, 'update']);
    Route::delete('/playlists/{id}', [PlaylistController::class, 'destroy']);
    Route::post('/playlists/{id}/songs', [PlaylistController::class, 'addSong']);
    Route::delete('/playlists/{id}/songs/{songId}', [PlaylistController::class, 'removeSong']);
    Route::post('/playlist/agregar-cancion', [PlaylistController::class, 'agregarCancion']);

    // Albums
    Route::get('/albums/dinamicos', [AlbumController::class, 'dynamicAlbums']);

    // Monetizacion
    Route::get('/monetizacion', [\App\Http\Controllers\MonetizacionController::class, 'getBalance']);
    Route::get('/artista/monetizacion', [\App\Http\Controllers\MonetizacionController::class, 'getArtistaMonetizacion']);
    Route::post('/monetizacion/retirar', [\App\Http\Controllers\MonetizacionController::class, 'retirar']);

    // Pasarela de Pagos (PayPal)
    Route::post('/payments/create-order', [PaymentController::class, 'createOrder']);
    Route::post('/payments/capture-order', [PaymentController::class, 'captureOrder']);
    Route::get('/artistas/{id}/earnings', [PaymentController::class, 'getArtistEarnings']);

    // Solicitudes de Trabajo / Colaboración
    Route::get('/colaboraciones/productor', [\App\Http\Controllers\ProductorSolicitudController::class, 'index']);
    Route::post('/colaboraciones/productor', [\App\Http\Controllers\ProductorSolicitudController::class, 'store']);
    Route::get('/colaboraciones/productor/{id}/postulantes', [\App\Http\Controllers\ProductorSolicitudController::class, 'getPostulantes']);
    Route::patch('/colaboraciones/productor/{id}/estado', [\App\Http\Controllers\ProductorSolicitudController::class, 'cambiarEstado']);
    Route::patch('/colaboraciones/productor/{id}/postulantes/{artistaId}/estado', [\App\Http\Controllers\ProductorSolicitudController::class, 'cambiarEstadoPostulante']);
    
    Route::get('/colaboraciones/activas', [\App\Http\Controllers\ProductorSolicitudController::class, 'listarTodasActivas']);
    Route::post('/colaboraciones/{id}/aplicar', [\App\Http\Controllers\ProductorSolicitudController::class, 'aplicarASolicitud']);
});
