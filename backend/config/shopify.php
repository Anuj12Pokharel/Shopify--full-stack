<?php

return [
    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET'),
    'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),
    'scopes' => env('SHOPIFY_SCOPES', 'read_products,write_products,read_orders'),
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
];
