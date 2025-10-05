<?php

return [
    'costs' => [
        'base_rate_per_km' => env('LOGISTICS_BASE_RATE_PER_KM', 1.35),
        'weight_rate_per_kg' => env('LOGISTICS_WEIGHT_RATE_PER_KG', 0.04),
        'volume_rate_per_m3' => env('LOGISTICS_VOLUME_RATE_PER_M3', 0.08),
        'handling_fee' => env('LOGISTICS_HANDLING_FEE', 30),
        'hazard_fee' => env('LOGISTICS_HAZARD_FEE', 95),
    ],
    'billing' => [
        'tax_rate' => env('LOGISTICS_TAX_RATE', 0.19),
        'prefix' => env('LOGISTICS_INVOICE_PREFIX', 'INV'),
    ],
    'inventory' => [
        'endpoint' => env('LOGISTICS_INVENTORY_ENDPOINT'),
        'token' => env('LOGISTICS_INVENTORY_TOKEN'),
    ],
];
