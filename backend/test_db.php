<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Attempting to create shop...\n";
    $shop = \App\Models\Shop::create([
        'shopify_domain' => 'my-test-store.myshopify.com',
        'access_token' => 'dummy_token'
    ]);
    echo "SUCCESS: Created shop " . $shop->id . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
