<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Obtener el token de los encabezados de la solicitud
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return response()->json([
                'message' => 'Authorization header not found'
            ], 401);
        }

        // Extraer el token (se espera que el formato sea "Bearer <token>")
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            // Decodificar el token usando la clave secreta
            $key = env('JWT_SECRET', 'your-secret-key');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Agregar la data del payload al objeto Request para que esté disponible en los controladores
            $request->attributes->add(['jwt_payload' => (array) $decoded]);

            // Configurar la conexión a la base de datos utilizando el valor 'db' del payload
            $data_base = $decoded->db;

            // Validar si el valor de la base de datos está presente
            if (!$data_base) {
                return response()->json([
                    'message' => 'No database specified in the payload',
                ], 400);
            }
            Config::set('database.connections.dynamic_database', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => "lempapco_" . $data_base,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            // Establecer la conexión dinámica
            DB::reconnect('dynamic_database');
        } catch (\PDOException $e) {
            // Error en la conexión a la base de datos
            return response()->json([
                'message' => 'Failed to connect to the database',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            // Error en el token o general
            return response()->json([
                'message' => 'Invalid or expired token',
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
