<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'shopify_product_id',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'status',
        'tags',
        'variants',
        'images',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'variants' => 'array',
        'images' => 'array',
        'published_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
