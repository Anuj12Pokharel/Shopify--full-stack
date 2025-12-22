<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * 
     * @OA\Get(
     *     path="/api/dashboard/stats",
     *     tags={"Dashboard"},
     *     summary="Get dashboard statistics",
     *     description="Returns total products, collections, orders, and last sync timestamp",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_products", type="integer", example=150),
     *             @OA\Property(property="total_collections", type="integer", example=10),
     *             @OA\Property(property="total_orders", type="integer", example=45),
     *             @OA\Property(property="last_sync_at", type="string", format="date-time", example="2025-12-23T01:00:00Z"),
     *             @OA\Property(property="collections_with_products", type="integer", example=120)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - No active session",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shop not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Shop not found")
     *         )
     *     )
     * )
     */
    public function stats(Request $request)
    {
        $shopId = session('shop_id');

        // BYPASS FOR LOCAL DEVELOPMENT
        if (!$shopId && env('APP_ENV') === 'local') {
            $shopId = Shop::first()->id ?? null;
            if ($shopId) {
                session(['shop_id' => $shopId]); // Set it for subsequent requests
            }
        }

        if (!$shopId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $shop = Shop::with(['products', 'collections', 'orders'])->find($shopId);

        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        return response()->json([
            'total_products' => $shop->products()->count(),
            'total_collections' => $shop->collections()->count(),
            'total_orders' => $shop->orders()->count(),
            'last_sync_at' => $shop->last_sync_at?->toIso8601String(),
            'collections_with_products' => $shop->collections()
                ->selectRaw('SUM(products_count) as total')
                ->value('total') ?? 0,
        ]);
    }
}
