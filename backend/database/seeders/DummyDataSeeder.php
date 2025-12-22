<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Collection;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a Test Shop
        $shop = Shop::firstOrCreate(
            ['shop_domain' => 'test-store.myshopify.com'],
            [
                'access_token' => 'test_token_' . uniqid(),
                'last_sync_at' => Carbon::now(),
            ]
        );

        echo "Shop ID: {$shop->id}\n";

        // 2. Create Dummy Products
        $products = [];
        for ($i = 1; $i <= 20; $i++) {
            $products[] = [
                'shop_id' => $shop->id,
                'shopify_product_id' => 'prod_' . uniqid(),
                'title' => 'Test Product ' . $i,
                'body_html' => '<p>This is a description for test product ' . $i . '</p>',
                'vendor' => 'Test Vendor',
                'product_type' => 'T-Shirt',
                'status' => $i % 3 == 0 ? 'archived' : ($i % 2 == 0 ? 'draft' : 'active'),
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now(),
                'variants' => json_encode([
                    [
                        'id' => rand(1000, 9999),
                        'price' => rand(10, 100) . '.00',
                        'sku' => 'SKU-' . $i
                    ]
                ]),
                'images' => json_encode([
                    [
                        'src' => 'https://placehold.co/600x400?text=Product+' . $i
                    ]
                ]),
            ];
        }
        Product::insert($products);
        echo "Inserted 20 Products\n";

        // 3. Create Dummy Collections
        $collections = [];
        for ($i = 1; $i <= 5; $i++) {
            $collections[] = [
                'shop_id' => $shop->id,
                'shopify_collection_id' => 'col_' . uniqid(),
                'title' => 'Collection ' . $i,
                'products_count' => rand(5, 50),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        Collection::insert($collections);
        echo "Inserted 5 Collections\n";

        // 4. Create Dummy Orders
        $orders = [];
        for ($i = 1; $i <= 10; $i++) {
            $orders[] = [
                'shop_id' => $shop->id,
                'shopify_order_id' => 'ord_' . uniqid(),
                'order_number' => 1000 + $i,
                'customer' => json_encode(['first_name' => 'John', 'last_name' => 'Doe']),
                'total_price' => rand(50, 500) . '.00',
                'financial_status' => 'paid',
                'created_at' => Carbon::now()->subDays(rand(0, 10)),
                'updated_at' => Carbon::now(),
            ];
        }
        Order::insert($orders);
        echo "Inserted 10 Orders\n";
    }
}
