<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VirtualTryOnController;
use App\Http\Controllers\VirtualModelController;

Route::get('/', function () {
    return redirect()->route('virtual-try-on');
});

// 🔥 RUTAS VIRTUAL TRY-ON
Route::get('/virtual-try-on', [VirtualTryOnController::class, 'show'])->name('virtual-try-on');
Route::post('/virtual-try-on/generate', [VirtualTryOnController::class, 'generate'])->name('virtual-try-on.generate');
Route::get('/virtual-try-on/status/{taskId}', [VirtualTryOnController::class, 'taskStatus'])->name('virtual-try-on.status');

// 🔥 RUTAS VIRTUAL MODEL
Route::get('/virtual-model', [VirtualModelController::class, 'show'])->name('virtual-model');
Route::post('/virtual-model/generate', [VirtualModelController::class, 'generate'])->name('virtual-model.generate');
Route::get('/virtual-model/status/{taskId}', [VirtualModelController::class, 'taskStatus'])->name('virtual-model.status');
