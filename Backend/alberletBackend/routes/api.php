<?php

use App\Http\Controllers\Api\AlberletController;
use Illuminate\Support\Facades\Route;

Route::get('/alberletek', [AlberletController::class, 'index']);
Route::get('/alberletek/{alberlet}', [AlberletController::class, 'show']);
Route::post('/alberletek', [AlberletController::class, 'store']);
