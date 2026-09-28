<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('tier', ['Silver', 'Gold', 'Platinum'])->default('Silver');
            $table->unsignedInteger('points')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('point_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'expired', 'bonus']);
            $table->integer('points'); // positive for earned, negative for redeemed
            $table->unsignedInteger('balance_after');
            $table->string('reference_type')->nullable(); // order, reward, bonus
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('points_required');
            $table->enum('reward_type', ['voucher_discount', 'free_shipping', 'merchandise'])->default('voucher_discount');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('promo_prefix')->nullable(); // e.g. RWD-
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('point_histories');
        Schema::dropIfExists('memberships');
    }
};

