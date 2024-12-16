<?php

namespace App\Http\Controllers;

use App\Models\UsersModel;
use Illuminate\Http\Request;

use Firebase\JWT\JWT;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $users = new UsersModel();
        $user = $users->login($request->code, $request->password);
        if (!$user) {
            return response()->json([
                'message' => 'invalid credentials',
            ], 401);
        }
        $payload = [
            'iss' => 'your-application',
            'db' => $user->bd,
            'code' => $user->codigo_usuario,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24,
        ];

        $key = env('JWT_SECRET', 'your-secret-key');

        $jwt = JWT::encode($payload, $key, 'HS256');

        return response()->json([
            'nombre' => $user->razon_social,
            'idEmpresa' => $user->id_empresa,
            'razon_social' => $user->nombre,
            'dv' => $user->dv,
            'empresa_nombre' => $user->nombre . ' ' . $user->nit_usuario . '-' . $user->dv,
            'token' => $jwt,
        ]);
    }
}
