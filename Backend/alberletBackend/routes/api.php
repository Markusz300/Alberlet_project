<?php

use App\Http\Controllers\AlberletController;
use App\Http\Controllers\VarosController;
use App\Http\Controllers\MegyeController;
use Illuminate\Support\Facades\Route;

Route::get('/alberletek', [AlberletController::class, 'index']);
Route::get('/alberletek/{alberlet}', [AlberletController::class, 'show']);
Route::apiResource('alberletek', AlberletController::class);
Route::get('/varosok', [VarosController::class, 'index']);
Route::get('/megyek', [MegyeController::class, 'index']);
Route::get('/users/check', [App\Http\Controllers\TulajdonosController::class, 'check']);
