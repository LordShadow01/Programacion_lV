<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\VerificationCodeMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            'rol' => 'required|in:oyente,artista,productor,entidad',
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'nombre_artistico' => $request->nombre_artistico,
            'nombre_productor' => $request->nombre_productor,
            'nombre_entidad' => $request->nombre_entidad,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'genero_musical' => $request->genero_musical,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        // For Sanctum, we can delete the current token
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada'
        ], 200);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'nombre' => 'string|max:255',
            'nombre_artistico' => 'nullable|string|max:255',
            'biografia' => 'nullable|string',
            'foto' => 'nullable|string',
        ]);

        $user->update($request->only(['nombre', 'nombre_artistico', 'biografia', 'foto']));

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            'new_password_confirmation' => 'required|string|same:new_password'
        ], [
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.regex' => 'La nueva contraseña debe incluir mayúsculas, minúsculas y números.',
            'new_password_confirmation.same' => 'Las contraseñas no coinciden.'
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.'
        ], 200);
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email'
        ], [
            'email.exists' => 'El correo electrónico no está registrado.'
        ]);

        $code = strval(rand(100000, 999999));
        
        // Guardar o actualizar el código de recuperación
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'code' => $code,
                'created_at' => now()->toDateTimeString()
            ]
        );

        // Enviar el correo electrónico real usando configuración de Laravel
        Log::info('Iniciando envío de correo para: ' . $request->email);

        try {
            Mail::to($request->email)->send(new VerificationCodeMail($code, $request->email));
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de recuperación: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'No se pudo enviar el correo electrónico. Detalle: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Código de recuperación enviado con éxito.'
        ], 200);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $reset = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$reset) {
            return response()->json([
                'message' => 'No se ha solicitado ningún código para este correo.'
            ], 422);
        }

        // Recuperar código y fecha
        $storedCode = $reset->code ?? null;
        $createdAt = $reset->created_at ?? null;

        if ($storedCode !== $request->code) {
            return response()->json([
                'message' => 'El código de verificación es incorrecto.'
            ], 422);
        }

        // Verificar validez de 10 minutos
        $isValid = false;
        if ($createdAt) {
            if (strtotime($createdAt) + 600 >= time()) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return response()->json([
                'message' => 'El código de verificación ha expirado (validez de 10 minutos).'
            ], 422);
        }

        return response()->json([
            'message' => 'Código verificado con éxito.'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'new_password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            'new_password_confirmation' => 'required|string|same:new_password'
        ], [
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.regex' => 'La nueva contraseña debe incluir mayúsculas, minúsculas y números.',
            'new_password_confirmation.same' => 'Las contraseñas no coinciden.'
        ]);

        // Volver a verificar el código
        $reset = DB::table('password_resets')->where('email', $request->email)->first();
        if (!$reset || ($reset->code ?? null) !== $request->code) {
            return response()->json([
                'message' => 'Operación no válida o código incorrecto.'
            ], 422);
        }

        // Verificar expiración
        $createdAt = $reset->created_at ?? null;
        $isValid = false;
        if ($createdAt) {
            if (strtotime($createdAt) + 600 >= time()) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return response()->json([
                'message' => 'El código de verificación ha expirado.'
            ], 422);
        }

        // Actualizar usuario
        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Eliminar código utilizado
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Contraseña reestablecida correctamente.'
        ], 200);
    }
}
