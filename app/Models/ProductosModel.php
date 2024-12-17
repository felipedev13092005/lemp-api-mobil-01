<?php

use Illuminate\Support\Facades\DB;

class ProductosModel
{
    public function index()
    {
        try {
            $productos = Db::connection('dynamic_database')->table('producto')->get();
            return response()->json($productos);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error querying the database',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
