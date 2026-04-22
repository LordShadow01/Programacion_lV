<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtistaController;
use App\Http\Controllers\CancionController;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('artistas', ArtistaController::class);
Route::apiResource('canciones', CancionController::class);
