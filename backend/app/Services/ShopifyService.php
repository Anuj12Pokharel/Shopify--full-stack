<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Shop;

class ShopifyService
{
    private string $apiVersion;

    public function __construct()
    {
        $this->apiVersion = config('shopify.api_version', '2024-01');
    }

    /**
     * Make a GraphQL request to Shopify Admin API
     */
    public function graphqlRequest(Shop $shop, string $query, array $variables = []): array
    {
        $url = "https://{$shop->shop_domain}/admin/api/{$this->apiVersion}/graphql.json";

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->access_token,
            'Content-Type' => 'application/json',
        ])->post($url, [
                    'query' => $query,
                    'variables' => $variables,
                ]);

        if ($response->failed()) {
            throw new \Exception('Shopify API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Fetch products from Shopify
     */
    public function fetchProducts(Shop $shop, int $limit = 50, ?string $cursor = null): array
    {
        $query = <<<'GQL'
        query($limit: Int!, $cursor: String) {
            products(first: $limit, after: $cursor) {
                edges {
                    node {
                        id
                        title
                        descriptionHtml
                        vendor
                        productType
                        status
                        tags
                        publishedAt
                        variants(first: 10) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                    sku
                                }
                            }
                        }
                        images(first: 5) {
                            edges {
                                node {
                                    id
                                    url
                                    altText
                                }
                            }
                        }
                    }
                    cursor
                }
                pageInfo {
                    hasNextPage
                    endCursor
                }
            }
        }
        GQL;

        return $this->graphqlRequest($shop, $query, [
            'limit' => $limit,
            'cursor' => $cursor,
        ]);
    }

    /**
     * Fetch collections from Shopify
     */
    public function fetchCollections(Shop $shop, int $limit = 50, ?string $cursor = null): array
    {
        $query = <<<'GQL'
        query($limit: Int!, $cursor: String) {
            collections(first: $limit, after: $cursor) {
                edges {
                    node {
                        id
                        title
                        descriptionHtml
                        handle
                        productsCount
                        publishedAt
                    }
                    cursor
                }
                pageInfo {
                    hasNextPage
                    endCursor
                }
            }
        }
        GQL;

        return $this->graphqlRequest($shop, $query, [
            'limit' => $limit,
            'cursor' => $cursor,
        ]);
    }

    /**
     * Fetch orders from Shopify
     */
    public function fetchOrders(Shop $shop, int $limit = 50, ?string $cursor = null): array
    {
        $query = <<<'GQL'
        query($limit: Int!, $cursor: String) {
            orders(first: $limit, after: $cursor) {
                edges {
                    node {
                        id
                        name
                        email
                        totalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        displayFinancialStatus
                        displayFulfillmentStatus
                        processedAt
                        lineItems(first: 20) {
                            edges {
                                node {
                                    id
                                    title
                                    quantity
                                    variant {
                                        id
                                        price
                                    }
                                }
                            }
                        }
                        customer {
                            id
                            firstName
                            lastName
                            email
                        }
                    }
                    cursor
                }
                pageInfo {
                    hasNextPage
                    endCursor
                }
            }
        }
        GQL;

        return $this->graphqlRequest($shop, $query, [
            'limit' => $limit,
            'cursor' => $cursor,
        ]);
    }

    /**
     * Register a webhook
     */
    public function registerWebhook(Shop $shop, string $topic, string $address): array
    {
        $query = <<<'GQL'
        mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!) {
            webhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
                webhookSubscription {
                    id
                    topic
                    endpoint {
                        __typename
                        ... on WebhookHttpEndpoint {
                            callbackUrl
                        }
                    }
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GQL;

        return $this->graphqlRequest($shop, $query, [
            'topic' => $topic,
            'webhookSubscription' => [
                'callbackUrl' => $address,
                'format' => 'JSON',
            ],
        ]);
    }
}
