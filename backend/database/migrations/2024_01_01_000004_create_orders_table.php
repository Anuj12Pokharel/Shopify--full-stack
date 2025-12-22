<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('shopify_order_id')->unique();
            $table->string('order_number');
            $table->string('email')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('financial_status')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->json('line_items')->nullable();
            $table->json('customer')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('shop_id');
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
