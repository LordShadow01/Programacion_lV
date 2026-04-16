<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\InscripcionController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::resource('alumnos', AlumnoController::class);
Route::resource('docentes', DocenteController::class);
Route::resource('materias', MateriaController::class);
Route::resource('matriculas', MatriculaController::class);
Route::resource('inscripciones', InscripcionController::class);
