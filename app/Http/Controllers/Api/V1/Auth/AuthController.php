<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BasicController;
use App\Http\Contains\HttpStatusCode;
use App\Http\Requests\PostAuth\PostAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BasicController
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Iniciar sesión y generar token de acceso",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="admin"),
    *              @OA\Property(property="device_name", type="string", example="navegador", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Autenticación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciales inválidas"
     *     )
     * )
     */
    public function login(PostAuth $request)
    {
        try {
            // Intentar autenticación con credenciales
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return $this->unauthorizedResponse('Las credenciales proporcionadas no son correctas.');
            }
            
            // Obtener usuario autenticado
            $user = User::where('email', $request->email)->firstOrFail();
            
            // Definir nombre del dispositivo
            $deviceName = $request->device_name ?? ($request->userAgent() ?? 'API Token');
            
            // Si se solicita sesión única, eliminar otros tokens
            if ($request->has('single_session') && $request->single_session) {
                $user->tokens()->delete();
            }
            
            // Crear token de acceso
            $token = $user->createToken($deviceName)->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'user' => $user,
            ], 'Inicio de sesión exitoso', HttpStatusCode::OK);
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Hubo un problema al procesar la solicitud: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Cerrar sesión y revocar token",
     *     tags={"Autenticación"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión finalizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesión cerrada correctamente")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            // Eliminar el token actual
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Cierre de sesión exitoso', HttpStatusCode::OK);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Hubo un problema al procesar la solicitud. Por favor, intente nuevamente.',
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

}