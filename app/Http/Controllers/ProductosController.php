<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'This is the index method of the ProductosController',
            'request' => $request->all(),
        ]);
    }
}
