<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', AuthController::class . '@login');
});
