<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'shopify_order_id',
        'order_number',
        'email',
        'total_price',
        'currency',
        'financial_status',
        'fulfillment_status',
        'line_items',
        'customer',
        'processed_at',
    ];

    protected $casts = [
        'line_items' => 'array',
        'customer' => 'array',
        'processed_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
