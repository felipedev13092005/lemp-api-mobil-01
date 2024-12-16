<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    public function index()
    {
        try {
            $clientes = DB::table('cliente')->get();
            return response()->json([
                'clientes' => $clientes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error querying the database',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
