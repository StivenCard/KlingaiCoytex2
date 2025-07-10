<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VirtualTryOnController;

Route::get('/virtual-try-on', [VirtualTryOnController::class, 'show'])->name('virtual-try-on');
Route::post('/virtual-try-on/generate', [VirtualTryOnController::class, 'generate'])->name('virtual-try-on.generate');
Route::get('/virtual-try-on/status/{taskId}', [VirtualTryOnController::class, 'taskStatus'])->name('virtual-try-on.status');


