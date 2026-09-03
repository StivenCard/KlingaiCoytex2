<?php

use App\Http\Controllers\AdminGenerationsController;
use App\Http\Controllers\ChatAi\ChatAiController;
use App\Http\Controllers\ImageToVideoController;
use App\Http\Controllers\OmniModelController;
use App\Http\Controllers\Servientrega\ServiEntregaController;
use App\Http\Controllers\VirtualModelController;
use App\Http\Controllers\VirtualTryOnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('virtual-try-on');
});

// RUTAS VIRTUAL TRY-ON
Route::get('/virtual-try-on', [VirtualTryOnController::class, 'show'])->name('virtual-try-on');
Route::post('/virtual-try-on/generate', [VirtualTryOnController::class, 'generate'])->name('virtual-try-on.generate');
Route::get('/virtual-try-on/status/{taskId}', [VirtualTryOnController::class, 'taskStatus'])->name('virtual-try-on.status');

//RUTAS OMNI TRY-ON
Route::get('/omni-try-on', [OmniModelController::class, 'show'])->name('omni-try-on');
Route::post('/omni-try-on/generate', [OmniModelController::class, 'generate'])->name('omni-try-on.generate');
Route::get('/omni-try-on/status/{taskId}', [OmniModelController::class, 'taskStatus'])->name('omni-try-on.status');

// RUTAS VIRTUAL MODEL
Route::get('/virtual-model', [VirtualModelController::class, 'show'])->name('virtual-model');
Route::post('/virtual-model/generate', [VirtualModelController::class, 'generate'])->name('virtual-model.generate');
Route::get('/virtual-model/status/{taskId}', [VirtualModelController::class, 'taskStatus'])->name('virtual-model.status');

// RUTAS PARA IMAGEN A VIDEO
Route::get('/image-to-video', [ImageToVideoController::class, 'show'])->name('image-to-video');
Route::post('/image-to-video/generate', [ImageToVideoController::class, 'generate'])->name('image-to-video.generate');
Route::get('/image-to-video/status/{taskId}', [ImageToVideoController::class, 'taskStatus'])->name('image-to-video.status');

// RUTAS DE ADMINISTRACIÓN
Route::prefix('admin')->name('admin.')->group(function () {
    // Ruta principal con parámetro opcional de sección
    Route::get('/generations', [AdminGenerationsController::class, 'index'])->name('generations');

    // Rutas para eliminación
    Route::delete('/generations/{type}/{id}', [AdminGenerationsController::class, 'deleteGeneration'])->name('generations.delete');
    Route::delete('/generations/all', [AdminGenerationsController::class, 'deleteAllGenerations'])->name('generations.delete-all');
});

//RUTAS PARA SERVIENTREGA
Route::get('/consultar-guia', [ServiEntregaController::class, 'consultaGuia'])->name('consultar-guia');

//RUTAS PARA CHAT CON AI
Route::get('/chat', [ChatAiController::class, 'index'])->name('chatai.chat');
Route::post('/chat/send', [ChatAiController::class, 'send'])->name('chatai.send');
