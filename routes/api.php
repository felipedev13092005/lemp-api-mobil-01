<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientesController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', AuthController::class . '@login');
});
Route::group(['prefix' => 'clientes'], function () {
    Route::get('/', ClientesController::class . '@index')->middleware(JwtMiddleware::class);
});
