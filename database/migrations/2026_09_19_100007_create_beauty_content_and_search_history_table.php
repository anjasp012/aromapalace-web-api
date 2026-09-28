<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beauty_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('beauty_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained('beauty_topics')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('author_name')->default('Aroma Palace Editor');
            $table->unsignedInteger('reading_time_minutes')->default(3);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_published')->default(true);
            $table->dateTime('published_at')->nullable();
            $table->json('related_product_ids')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'is_trending']);
        });

        Schema::create('user_search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('keyword');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_search_histories');
        Schema::dropIfExists('beauty_articles');
        Schema::dropIfExists('beauty_topics');
    }
};

