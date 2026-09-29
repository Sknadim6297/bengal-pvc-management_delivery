<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name', 120);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('print_qualities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('print_services')->cascadeOnDelete();
            $table->string('slug', 40);
            $table->string('name', 120);
            $table->string('description', 255)->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['service_id', 'slug']);
            $table->index(['service_id', 'enabled']);
        });

        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_id')->constrained('print_qualities')->cascadeOnDelete();
            $table->unsignedInteger('min_quantity');
            $table->unsignedInteger('max_quantity')->nullable();
            $table->decimal('base_unit_price', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->index(['quality_id', 'enabled', 'min_quantity', 'max_quantity'], 'pricing_tiers_lookup_index');
        });

        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->unique()->constrained('print_services')->cascadeOnDelete();
            $table->decimal('flat_rate', 10, 2);
            $table->unsignedInteger('free_shipping_threshold')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('card_cover_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->unique()->constrained('print_services')->cascadeOnDelete();
            $table->decimal('price_per_card', 10, 2);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 32)->unique();
            $table->string('service_type', 40);
            $table->string('status', 32)->default('pending');
            $table->string('payment_status', 32)->default('pending');
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('cover_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->json('delivery_address')->nullable();
            $table->string('submission_key', 64)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at'], 'orders_user_created_index');
            $table->index(['user_id', 'status', 'created_at'], 'orders_user_status_created_index');
            $table->index(['service_type', 'status', 'created_at'], 'orders_service_status_created_index');
            $table->index(['payment_status', 'created_at'], 'orders_payment_created_index');
            $table->unique(['user_id', 'submission_key'], 'orders_user_submission_unique');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('quality_slug', 40);
            $table->string('quality_name', 120);
            $table->string('option_slug', 40)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('base_unit_price', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->boolean('cover_selected')->default(false);
            $table->decimal('cover_unit_price', 10, 2)->default(0);
            $table->decimal('cover_total', 12, 2)->default(0);
            $table->timestamps();
            $table->index('order_id');
        });

        Schema::create('order_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16);
            $table->string('original_name')->nullable();
            $table->string('storage_path')->nullable();
            $table->text('drive_url')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->string('status', 24)->default('received');
            $table->timestamps();
            $table->index(['order_id', 'type']);
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('old_status', 32)->nullable();
            $table->string('new_status', 32);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 60)->nullable();
            $table->string('transaction_id', 120)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 24)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_files');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('card_cover_settings');
        Schema::dropIfExists('shipping_settings');
        Schema::dropIfExists('pricing_tiers');
        Schema::dropIfExists('print_qualities');
        Schema::dropIfExists('print_services');
    }
};