<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Initiate OAuth installation
     */
    public function install(Request $request)
    {
        $shop = $request->query('shop');

        if (!$shop || !$this->isValidShopDomain($shop)) {
            return response()->json(['error' => 'Invalid shop domain'], 400);
        }

        $apiKey = config('shopify.api_key');
        $scopes = config('shopify.scopes');
        $redirectUri = config('app.url') . '/auth/callback';
        $nonce = Str::random(32);

        // Store nonce in session for verification
        session(['shopify_nonce' => $nonce, 'shop_domain' => $shop]);

        $installUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query([
            'client_id' => $apiKey,
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
            'state' => $nonce,
        ]);

        return redirect($installUrl);
    }

    /**
     * Handle OAuth callback
     */
    public function callback(Request $request)
    {
        $shop = $request->query('shop');
        $code = $request->query('code');
        $state = $request->query('state');

        // Verify state
        if ($state !== session('shopify_nonce')) {
            return response()->json(['error' => 'Invalid state parameter'], 400);
        }

        // Verify HMAC
        if (!$this->verifyHmac($request->query())) {
            return response()->json(['error' => 'HMAC verification failed'], 400);
        }

        try {
            // Exchange code for access token
            $response = Http::post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => config('shopify.api_key'),
                'client_secret' => config('shopify.api_secret'),
                'code' => $code,
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to get access token');
            }

            $data = $response->json();

            // Store shop credentials
            $shopModel = Shop::updateOrCreate(
                ['shop_domain' => $shop],
                [
                    'access_token' => $data['access_token'],
                    'scope' => $data['scope'],
                ]
            );

            // Store shop ID in session
            session(['shop_id' => $shopModel->id]);

            // Redirect to embedded app
            $frontendUrl = config('shopify.frontend_url', config('app.url'));
            return redirect("{$frontendUrl}?shop={$shop}");

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify HMAC signature
     */
    private function verifyHmac(array $params): bool
    {
        $hmac = $params['hmac'] ?? '';
        unset($params['hmac']);

        ksort($params);
        $queryString = http_build_query($params);

        $calculatedHmac = hash_hmac('sha256', $queryString, config('shopify.api_secret'));

        return hash_equals($hmac, $calculatedHmac);
    }

    /**
     * Validate shop domain format
     */
    private function isValidShopDomain(string $shop): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop);
    }
}
