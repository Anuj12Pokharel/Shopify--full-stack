<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\SyncService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    private SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Trigger manual product sync
     * 
     * @OA\Post(
     *     path="/api/sync/products",
     *     tags={"Sync"},
     *     summary="Sync products from Shopify",
     *     description="Manually trigger product synchronization from Shopify store",
     *     @OA\Response(
     *         response=200,
     *         description="Sync completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="synced_count", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Shop not found")
     * )
     */
    public function syncProducts(Request $request)
    {
        $shopId = session('shop_id');

        // BYPASS FOR LOCAL DEVELOPMENT
        if (!$shopId && env('APP_ENV') === 'local') {
            $shopId = Shop::first()->id ?? null;
            if ($shopId) {
                session(['shop_id' => $shopId]);
            }
        }

        if (!$shopId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $shop = Shop::find($shopId);

        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        $result = $this->syncService->syncProducts($shop);

        return response()->json($result);
    }

    /**
     * Trigger manual collection sync
     * 
     * @OA\Post(
     *     path="/api/sync/collections",
     *     tags={"Sync"},
     *     summary="Sync collections from Shopify",
     *     description="Manually trigger collection synchronization from Shopify store",
     *     @OA\Response(
     *         response=200,
     *         description="Sync completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="synced_count", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Shop not found")
     * )
     */
    public function syncCollections(Request $request)
    {
        $shopId = session('shop_id');

        // BYPASS FOR LOCAL DEVELOPMENT
        if (!$shopId && env('APP_ENV') === 'local') {
            $shopId = Shop::first()->id ?? null;
            if ($shopId) {
                session(['shop_id' => $shopId]);
            }
        }

        if (!$shopId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $shop = Shop::find($shopId);

        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        $result = $this->syncService->syncCollections($shop);

        return response()->json($result);
    }

    /**
     * Trigger manual order sync
     * 
     * @OA\Post(
     *     path="/api/sync/orders",
     *     tags={"Sync"},
     *     summary="Sync orders from Shopify",
     *     description="Manually trigger order synchronization from Shopify store",
     *     @OA\Response(
     *         response=200,
     *         description="Sync completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="synced_count", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Shop not found")
     * )
     */
    public function syncOrders(Request $request)
    {
        $shopId = session('shop_id');

        // BYPASS FOR LOCAL DEVELOPMENT
        if (!$shopId && env('APP_ENV') === 'local') {
            $shopId = Shop::first()->id ?? null;
            if ($shopId) {
                session(['shop_id' => $shopId]);
            }
        }

        if (!$shopId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $shop = Shop::find($shopId);

        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        $result = $this->syncService->syncOrders($shop);

        return response()->json($result);
    }
}
