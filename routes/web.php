<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageToVideoController;
use App\Http\Controllers\VirtualModelController;
use App\Http\Controllers\VirtualTryOnController;
use App\Http\Controllers\AdminGenerationsController;

Route::get('/', function () {
    return redirect()->route('virtual-try-on');
});

// RUTAS VIRTUAL TRY-ON
Route::get('/virtual-try-on', [VirtualTryOnController::class, 'show'])->name('virtual-try-on');
Route::post('/virtual-try-on/generate', [VirtualTryOnController::class, 'generate'])->name('virtual-try-on.generate');
Route::get('/virtual-try-on/status/{taskId}', [VirtualTryOnController::class, 'taskStatus'])->name('virtual-try-on.status');

// RUTAS VIRTUAL MODEL
Route::get('/virtual-model', [VirtualModelController::class, 'show'])->name('virtual-model');
Route::post('/virtual-model/generate', [VirtualModelController::class, 'generate'])->name('virtual-model.generate');
Route::get('/virtual-model/status/{taskId}', [VirtualModelController::class, 'taskStatus'])->name('virtual-model.status');

// RUTAS PARA IMAGEN A VIDEO
Route::get('/image-to-video', [ImageToVideoController::class, 'show'])->name('image-to-video');
Route::post('/image-to-video/generate', [ImageToVideoController::class, 'generate'])->name('image-to-video.generate');
Route::get('/image-to-video/status/{taskId}', [ImageToVideoController::class, 'taskStatus'])->name('image-to-video.status');

// RUTAS DE ADMINISTRACIÓN
Route::prefix('admin')->group(function () {
    Route::get('/generations', [AdminGenerationsController::class, 'index'])->name('admin.generations');
    Route::delete('/generations/{type}/{id}', [AdminGenerationsController::class, 'deleteGeneration'])->name('admin.generations.delete');
    Route::delete('/generations/all', [AdminGenerationsController::class, 'deleteAllGenerations'])->name('admin.generations.delete.all');
});