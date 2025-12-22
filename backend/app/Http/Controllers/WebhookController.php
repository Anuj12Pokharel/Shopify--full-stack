<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle product created webhook
     */
    public function productCreated(Request $request)
    {
        try {
            $shopDomain = $request->header('X-Shopify-Shop-Domain');
            $shop = Shop::where('shop_domain', $shopDomain)->first();

            if (!$shop) {
                return response()->json(['error' => 'Shop not found'], 404);
            }

            $productData = $request->all();

            Product::create([
                'shop_id' => $shop->id,
                'shopify_product_id' => $productData['id'],
                'title' => $productData['title'],
                'body_html' => $productData['body_html'] ?? '',
                'vendor' => $productData['vendor'] ?? '',
                'product_type' => $productData['product_type'] ?? '',
                'status' => strtolower($productData['status'] ?? 'active'),
                'tags' => explode(', ', $productData['tags'] ?? ''),
                'variants' => $productData['variants'] ?? [],
                'images' => $productData['images'] ?? [],
                'published_at' => $productData['published_at'] ?? null,
            ]);

            Log::info("Product created via webhook: {$productData['title']}");

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('Webhook product created failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle product updated webhook
     */
    public function productUpdated(Request $request)
    {
        try {
            $shopDomain = $request->header('X-Shopify-Shop-Domain');
            $shop = Shop::where('shop_domain', $shopDomain)->first();

            if (!$shop) {
                return response()->json(['error' => 'Shop not found'], 404);
            }

            $productData = $request->all();

            $product = Product::where('shop_id', $shop->id)
                ->where('shopify_product_id', $productData['id'])
                ->first();

            if ($product) {
                $product->update([
                    'title' => $productData['title'],
                    'body_html' => $productData['body_html'] ?? '',
                    'vendor' => $productData['vendor'] ?? '',
                    'product_type' => $productData['product_type'] ?? '',
                    'status' => strtolower($productData['status'] ?? 'active'),
                    'tags' => explode(', ', $productData['tags'] ?? ''),
                    'variants' => $productData['variants'] ?? [],
                    'images' => $productData['images'] ?? [],
                    'published_at' => $productData['published_at'] ?? null,
                ]);

                Log::info("Product updated via webhook: {$productData['title']}");
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('Webhook product updated failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle product deleted webhook
     */
    public function productDeleted(Request $request)
    {
        try {
            $shopDomain = $request->header('X-Shopify-Shop-Domain');
            $shop = Shop::where('shop_domain', $shopDomain)->first();

            if (!$shop) {
                return response()->json(['error' => 'Shop not found'], 404);
            }

            $productData = $request->all();

            Product::where('shop_id', $shop->id)
                ->where('shopify_product_id', $productData['id'])
                ->delete();

            Log::info("Product deleted via webhook: {$productData['id']}");

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('Webhook product deleted failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
