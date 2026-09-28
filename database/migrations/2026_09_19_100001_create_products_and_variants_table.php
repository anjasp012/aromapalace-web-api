<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->text('how_to_use')->nullable();
            $table->text('ingredients')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->integer('discount_percent')->default(0);
            $table->integer('stock')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'is_popular']);
            $table->index(['is_active', 'is_discount']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image_url');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name'); // e.g. "50 ml", "100 ml"
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('attribute_name')->nullable(); // e.g. "Size", "Shade"
            $table->string('attribute_value')->nullable(); // e.g. "100ml"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};

