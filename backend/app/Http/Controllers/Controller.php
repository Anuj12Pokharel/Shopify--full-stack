<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Shopify Embedded App API",
 *     version="1.0.0",
 *     description="API for managing Shopify products, collections, and orders",
 *     @OA\Contact(
 *         email="support@shopifyapp.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
