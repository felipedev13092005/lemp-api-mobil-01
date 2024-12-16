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
        ];

        $key = env('JWT_SECRET', 'your-secret-key');

        $jwt = JWT::encode($payload, $key, 'HS256');
        // {
        //     "nombre": "BARVAL COLOMBIA SA",
        //     "idEmpresa": "1900600",
        //     "razon_social": "BARVAL COLOMBIA SA",
        //     "dv": "0",
        //     "empresa_nombre": "BARVAL COLOMBIA SA 800028532-0",
        //     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuaXQiOiI4MDAwMjg1MzIiLCJjb2RpZ28iOiIxOTAwNjAwIiwiZGF0YWJhc2UiOiJiZDE5MDA2MDAiLCJ0aW1lU3RhbXAiOiIyMDI0LTEyLTE2IDE1OjMzOjQyIn0.E3DKZ2YeohTfyaKZ0CIxgjuJCkyFiMg8CbpHNuPFso8"
        //   }
        // {
        //     "data": {
        //       "codigo_usuario": 1900600,
        //       "nombre": "  BARVAL COLOMBIA SA",
        //       "razon_social": "BARVAL COLOMBIA SA",
        //       "bd": "bd1900600",
        //       "tipo_usuario": 2,
        //       "dv": 0,
        //       "tipo": 31,
        //       "fecha_fin": "2025-05-20",
        //       "nit_usuario": 800028532,
        //       "id_empresa": 1900600
        //     },
        //     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5b3VyLWFwcGxpY2F0aW9uIiwiZGIiOiJiZDE5MDA2MDAiLCJjb2RlIjoxOTAwNjAwLCJpYXQiOjE3MzQzODIxMjR9.SFrtuWmhdzgIDmnfTwPWxNXF5jtxCoc-4egwz4w5nm4"
        //   }
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
