<?php

use App\Http\Controllers\Api\AlberletController;
use App\Http\Controllers\MegyeController;
use App\Http\Controllers\Api\VarosController;
use Illuminate\Support\Facades\Route;

Route::get('/alberletek', [AlberletController::class, 'index']);
Route::get('/alberletek/{alberlet}', [AlberletController::class, 'show']);
Route::apiResource('alberletek', AlberletController::class);
Route::get('/varosok', [VarosController::class, 'index']);
Route::get('/megyek', [MegyeController::class, 'index']);
