<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Product;
use App\Models\Collection;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncService
{
    private ShopifyService $shopifyService;

    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    /**
     * Sync products from Shopify to local database
     */
    public function syncProducts(Shop $shop): array
    {
        $synced = 0;
        $cursor = null;
        $hasNextPage = true;

        try {
            while ($hasNextPage) {
                $response = $this->shopifyService->fetchProducts($shop, 50, $cursor);

                if (isset($response['errors'])) {
                    throw new \Exception(json_encode($response['errors']));
                }

                $products = $response['data']['products']['edges'] ?? [];
                $pageInfo = $response['data']['products']['pageInfo'] ?? [];

                foreach ($products as $edge) {
                    $productData = $edge['node'];

                    Product::updateOrCreate(
                        [
                            'shop_id' => $shop->id,
                            'shopify_product_id' => $this->extractId($productData['id']),
                        ],
                        [
                            'title' => $productData['title'],
                            'body_html' => $productData['descriptionHtml'] ?? '',
                            'vendor' => $productData['vendor'] ?? '',
                            'product_type' => $productData['productType'] ?? '',
                            'status' => strtolower($productData['status']),
                            'tags' => $productData['tags'] ?? [],
                            'variants' => $this->formatVariants($productData['variants']['edges'] ?? []),
                            'images' => $this->formatImages($productData['images']['edges'] ?? []),
                            'published_at' => $productData['publishedAt'] ?? null,
                        ]
                    );

                    $synced++;
                }

                $hasNextPage = $pageInfo['hasNextPage'] ?? false;
                $cursor = $pageInfo['endCursor'] ?? null;
            }

            $shop->update(['last_sync_at' => now()]);

            return [
                'success' => true,
                'synced' => $synced,
                'message' => "Successfully synced {$synced} products",
            ];
        } catch (\Exception $e) {
            Log::error('Product sync failed: ' . $e->getMessage());
            return [
                'success' => false,
                'synced' => $synced,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync collections from Shopify to local database
     */
    public function syncCollections(Shop $shop): array
    {
        $synced = 0;
        $cursor = null;
        $hasNextPage = true;

        try {
            while ($hasNextPage) {
                $response = $this->shopifyService->fetchCollections($shop, 50, $cursor);

                if (isset($response['errors'])) {
                    throw new \Exception(json_encode($response['errors']));
                }

                $collections = $response['data']['collections']['edges'] ?? [];
                $pageInfo = $response['data']['collections']['pageInfo'] ?? [];

                foreach ($collections as $edge) {
                    $collectionData = $edge['node'];

                    Collection::updateOrCreate(
                        [
                            'shop_id' => $shop->id,
                            'shopify_collection_id' => $this->extractId($collectionData['id']),
                        ],
                        [
                            'title' => $collectionData['title'],
                            'body_html' => $collectionData['descriptionHtml'] ?? '',
                            'handle' => $collectionData['handle'] ?? '',
                            'products_count' => $collectionData['productsCount'] ?? 0,
                            'published_at' => $collectionData['publishedAt'] ?? null,
                        ]
                    );

                    $synced++;
                }

                $hasNextPage = $pageInfo['hasNextPage'] ?? false;
                $cursor = $pageInfo['endCursor'] ?? null;
            }

            return [
                'success' => true,
                'synced' => $synced,
                'message' => "Successfully synced {$synced} collections",
            ];
        } catch (\Exception $e) {
            Log::error('Collection sync failed: ' . $e->getMessage());
            return [
                'success' => false,
                'synced' => $synced,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync orders from Shopify to local database
     */
    public function syncOrders(Shop $shop): array
    {
        $synced = 0;
        $cursor = null;
        $hasNextPage = true;

        try {
            while ($hasNextPage) {
                $response = $this->shopifyService->fetchOrders($shop, 50, $cursor);

                if (isset($response['errors'])) {
                    throw new \Exception(json_encode($response['errors']));
                }

                $orders = $response['data']['orders']['edges'] ?? [];
                $pageInfo = $response['data']['orders']['pageInfo'] ?? [];

                foreach ($orders as $edge) {
                    $orderData = $edge['node'];

                    Order::updateOrCreate(
                        [
                            'shop_id' => $shop->id,
                            'shopify_order_id' => $this->extractId($orderData['id']),
                        ],
                        [
                            'order_number' => $orderData['name'],
                            'email' => $orderData['email'] ?? '',
                            'total_price' => $orderData['totalPriceSet']['shopMoney']['amount'] ?? 0,
                            'currency' => $orderData['totalPriceSet']['shopMoney']['currencyCode'] ?? 'USD',
                            'financial_status' => $orderData['displayFinancialStatus'] ?? '',
                            'fulfillment_status' => $orderData['displayFulfillmentStatus'] ?? '',
                            'line_items' => $this->formatLineItems($orderData['lineItems']['edges'] ?? []),
                            'customer' => $orderData['customer'] ?? null,
                            'processed_at' => $orderData['processedAt'] ?? null,
                        ]
                    );

                    $synced++;
                }

                $hasNextPage = $pageInfo['hasNextPage'] ?? false;
                $cursor = $pageInfo['endCursor'] ?? null;
            }

            return [
                'success' => true,
                'synced' => $synced,
                'message' => "Successfully synced {$synced} orders",
            ];
        } catch (\Exception $e) {
            Log::error('Order sync failed: ' . $e->getMessage());
            return [
                'success' => false,
                'synced' => $synced,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Extract numeric ID from Shopify GID
     */
    private function extractId(string $gid): string
    {
        $parts = explode('/', $gid);
        return end($parts);
    }

    /**
     * Format variants for storage
     */
    private function formatVariants(array $edges): array
    {
        return array_map(function ($edge) {
            return [
                'id' => $this->extractId($edge['node']['id']),
                'title' => $edge['node']['title'],
                'price' => $edge['node']['price'],
                'sku' => $edge['node']['sku'] ?? '',
            ];
        }, $edges);
    }

    /**
     * Format images for storage
     */
    private function formatImages(array $edges): array
    {
        return array_map(function ($edge) {
            return [
                'id' => $this->extractId($edge['node']['id']),
                'url' => $edge['node']['url'],
                'alt' => $edge['node']['altText'] ?? '',
            ];
        }, $edges);
    }

    /**
     * Format line items for storage
     */
    private function formatLineItems(array $edges): array
    {
        return array_map(function ($edge) {
            return [
                'id' => $this->extractId($edge['node']['id']),
                'title' => $edge['node']['title'],
                'quantity' => $edge['node']['quantity'],
                'price' => $edge['node']['variant']['price'] ?? 0,
            ];
        }, $edges);
    }
}
