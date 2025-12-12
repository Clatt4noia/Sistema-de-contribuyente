<?php

use App\Domains\Billing\Http\Controllers\SunatWebhookController;
use App\Http\Controllers\DispatchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/sunat/status-callback', SunatWebhookController::class)->name('sunat.webhook');

// Rutas GRE (Guías de Remisión)
Route::prefix('gre')->group(function () {
    Route::post('/', [DispatchController::class, 'store']);
    Route::post('/{id}/firmar', [DispatchController::class, 'firmar']);
    Route::post('/{id}/enviar', [DispatchController::class, 'enviar']);
});
