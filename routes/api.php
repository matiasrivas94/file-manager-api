<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FolderController;
use App\Http\Controllers\API\FileController;

// RUTA TEST
Route::get('/', fn() => response()->json(['message' => 'API funcionando correctamente!']));

// CARPETAS
Route::apiResource('folders', FolderController::class);

// ARCHIVOS
Route::apiResource('files', FileController::class);

// RESTAURAR ARCHIVO ELIMINADO
Route::post('files/{id}/restore', [FileController::class, 'restore']);

// ELIMINAR DEFINITIVAMENTE UN ARCHUVO
Route::delete('files/{id}/force', [FileController::class, 'forceDelete']);
