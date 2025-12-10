<?php

use App\Domains\Billing\Http\Controllers\SunatWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/sunat/status-callback', SunatWebhookController::class)->name('sunat.webhook');
