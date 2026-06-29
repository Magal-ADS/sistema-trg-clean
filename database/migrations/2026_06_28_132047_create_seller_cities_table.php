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
        Schema::create('seller_cities', function (Blueprint $table) {
            $table->id();
            $table->string('glide_id')->nullable()->unique();
            $table->string('seller_name');
            $table->string('seller_email')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_cities');
    }
};
