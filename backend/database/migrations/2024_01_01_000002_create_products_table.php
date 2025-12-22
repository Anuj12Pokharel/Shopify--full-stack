<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('shopify_product_id')->unique();
            $table->string('title');
            $table->text('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('status')->default('active'); // active, draft, archived
            $table->json('tags')->nullable();
            $table->json('variants')->nullable();
            $table->json('images')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('shop_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
