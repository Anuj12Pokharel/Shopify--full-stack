<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_domain',
        'access_token',
        'scope',
        'last_sync_at',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
