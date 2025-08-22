<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FolderController;
use App\Http\Controllers\API\FileController;

// RUTA TEST
Route::get('/', fn() => response()->json(['message' => 'API funcionando correctamente!']));

// CARPETAS
Route::apiResource('folders', FolderController::class);
Route::get('/folders/{id}/info', [FolderController::class, 'info']);

// ARCHIVOS
Route::prefix('files')->group(function () {
    Route::get('/', [FileController::class, 'index']);
    Route::post('/', [FileController::class, 'store']);
    Route::delete('/{id}', [FileController::class, 'destroy']); // Borrado lógico
    Route::post('/{id}/restore', [FileController::class, 'restore']); // Restaurar
    Route::delete('/{id}/force', [FileController::class, 'forceDelete']); // Borrado definitivo
    Route::get('/{id}/download', [FileController::class, 'download']);
});