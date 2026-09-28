<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image_url');
            $table->string('mobile_image_url')->nullable();
            $table->string('link_type')->default('none'); // product, category, brand, promo, url, none
            $table->string('link_target')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. AROMA20
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_purchase', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->unsignedInteger('quota')->default(100);
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->text('terms_conditions')->nullable();
            $table->boolean('is_exclusive')->default(false); // e.g. member exclusive
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('banners');
    }
};

