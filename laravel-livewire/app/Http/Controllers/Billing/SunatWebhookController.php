<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\SunatLog;
use Illuminate\Http\Request;

class SunatWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->all();

        SunatLog::create([
            'operation' => 'webhook',
            'endpoint' => 'webhook',
            'request_payload' => json_encode($payload),
            'response_payload' => null,
            'status_code' => 'WEBHOOK',
            'is_success' => true,
            'executed_at' => now(),
        ]);

        return response()->json(['message' => 'Webhook recibido'], 202);
    }
}
