<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    // Upload para archivos de audio
    public function uploadAudio(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('audio', 'public');
            
            return response()->json([
                'url'  => '/storage/' . $path,   // relative — works on any port
                'name' => $file->getClientOriginalName()
            ]);
        }

        return response()->json(['error' => 'No se pudo subir el archivo'], 400);
    }

    public function uploadImage(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'No se recibió ningún archivo.'], 400);
            }

            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            ]);

            $file = $request->file('file');
            $path = $file->store('images', 'public');
            
            return response()->json([
                'url'  => '/storage/' . $path,   // relative — works on any port
                'name' => $file->getClientOriginalName()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Error de validación del archivo',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor: ' . $e->getMessage()], 500);
        }
    }

    // Mantener método legacy para compatibilidad (deprecated)
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $folder = in_array(strtolower($extension), ['mp3', 'wav', 'aac']) ? 'audio' : (in_array(strtolower($extension), ['mp4', 'mov', 'avi']) ? 'video' : 'images');
            
            $path = $file->store($folder, 'public');
            return response()->json([
                'url' => Storage::url($path),
                'name' => $file->getClientOriginalName()
            ]);
        }

        return response()->json(['error' => 'No se pudo subir el archivo'], 400);
    }
}
