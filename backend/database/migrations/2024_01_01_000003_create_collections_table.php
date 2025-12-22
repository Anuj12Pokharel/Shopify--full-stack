<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('shopify_collection_id')->unique();
            $table->string('title');
            $table->text('body_html')->nullable();
            $table->string('handle')->nullable();
            $table->integer('products_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
