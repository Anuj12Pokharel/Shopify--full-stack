<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get paginated products with search and filter
     * 
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="List all products",
     *     description="Get paginated list of products with optional search and status filter",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search products by title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by product status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "draft", "archived"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="shopify_product_id", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="per_page", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
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

        $query = Product::where('shop_id', $shopId);

        // Search by title
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', strtolower($request->status));
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Get a single product
     * 
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Get product by ID",
     *     description="Returns a single product",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="shopify_product_id", type="string"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="variants", type="object"),
     *             @OA\Property(property="images", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(Request $request, $id)
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

        $product = Product::where('shop_id', $shopId)->findOrFail($id);

        return response()->json($product);
    }
}
