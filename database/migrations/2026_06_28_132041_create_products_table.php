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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('glide_id')->nullable()->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->foreignId('size_category_id')->nullable()->constrained('size_categories')->nullOnDelete();
            $table->string('sku')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('promotional_price', 12, 2)->nullable();
            $table->boolean('has_variation')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_unavailable')->default(false);
            $table->unsignedInteger('stock')->default(0);
            $table->text('image_url')->nullable();
            $table->json('images')->nullable();
            $table->json('variations')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'sub_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
