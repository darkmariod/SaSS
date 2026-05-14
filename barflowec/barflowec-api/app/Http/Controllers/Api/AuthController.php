<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inicio de sesión con cookies httpOnly (Sanctum SPA).
     *
     * Flujo SPA (con sesión):
     * 1. Frontend llama a GET /sanctum/csrf-cookie (setea XSRF-TOKEN)
     * 2. Frontend envía POST /api/login con credenciales
     * 3. Este método autentica, regenera sesión y setea cookie httpOnly
     * 4. Todas las requests subsecuentes usan la cookie de sesión
     *
     * Para requests sin sesión (tests, API clients):
     * - Autentica al usuario sin regenerar sesión
     * - Las rutas protegidas con auth:sanctum siguen funcionando vía token
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas.'],
            ]);
        }

        // Regenerar sesión solo si está disponible (SPA mode)
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Login correcto.',
            'user' => $request->user(),
        ]);
    }

    /**
     * Retorna el usuario autenticado vía cookie de sesión o token.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Cierra sesión.
     * Invalida sesión si está disponible (SPA mode).
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
