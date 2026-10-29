<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->string('image')->default('default.jpg');
            $table->json('blurb')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('physical')->index();
            $table->string('kind')->default('product')->index();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('brand')->nullable();
            $table->json('description');
            $table->json('highlights');
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('badge')->nullable();
            $table->string('image');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_deal')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('book_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('author');
            $table->unsignedInteger('pages')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->json('title');
            $table->unsignedInteger('sort')->default(0);
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->json('name');
            $table->json('hint')->nullable();
            $table->boolean('collects_on_delivery')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->json('name');
            $table->json('eta')->nullable(); // delivery time or days to deliver
            $table->decimal('fee', 10, 2);
            $table->decimal('free_over', 10, 2)->nullable();
            $table->json('governorates');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('governorate');
            $table->string('city');
            $table->string('address');
            $table->string('notes')->nullable();
            $table->string('payment_method');
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_method_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('payment_status')->default('pending')->index();
            $table->string('delivery_status')->default('pending')->index();
            $table->string('promo_code')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('placed');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_type')->default('physical');
            $table->json('name');
            $table->string('image')->nullable();
            $table->unsignedInteger('qty');
            $table->decimal('price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('merchant_reference')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->string('status')->default('pending');
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->json('payload')->nullable();
            $table->json('metadata')->nullable();
            $table->text('checkout_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->unique('transaction_id');
            $table->unique('merchant_reference');
            $table->index(['gateway', 'merchant_reference']);
        });

        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->string('status')->default('held')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamp('granted_at');
            $table->timestamps();

            $table->unique('order_item_id');
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('excerpt');
            $table->json('body');
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body');
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('role')->nullable();
            $table->json('quote');
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('locale', 5)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('news');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('entitlements');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('book_details');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
