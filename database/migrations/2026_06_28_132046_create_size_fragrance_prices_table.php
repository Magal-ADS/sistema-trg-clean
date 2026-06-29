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
        Schema::create('size_fragrance_prices', function (Blueprint $table) {
            $table->id();
            $table->string('glide_id')->nullable()->unique();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fragrance_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('color_type_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('promotional_price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'size_id', 'fragrance_type_id', 'color_type_id'], 'product_variation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_fragrance_prices');
    }
};
