<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('fulfillment_type', ['home_delivery', 'store_pickup'])->default('home_delivery');
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('user_addresses')->nullOnDelete();
            $table->json('shipping_address_snapshot')->nullable();
            $table->string('shipping_courier')->nullable();
            $table->string('shipping_service')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->string('estimated_delivery')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('promo_code')->nullable();
            $table->string('payment_method')->default('bca_va'); // bca_va, mandiri_va, qris, credit_card, cod
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->enum('order_status', [
                'pending_payment',
                'processing',
                'shipped',
                'ready_for_pickup',
                'completed',
                'cancelled'
            ])->default('pending_payment');
            $table->string('tracking_number')->nullable();
            $table->string('pickup_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'order_status']);
            $table->index('order_number');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('product_image')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('status');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('payment_gateway')->default('internal_simulator');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('payment_type'); // bca_va, mandiri_va, qris, credit_card, cod
            $table->decimal('gross_amount', 12, 2);
            $table->enum('transaction_status', ['pending', 'settlement', 'expire', 'cancel', 'failure'])->default('pending');
            $table->string('va_number')->nullable();
            $table->text('qr_string')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

