<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_account_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date')->index();
            $table->unsignedInteger('presential_count')->default(0);
            $table->unsignedInteger('instagram_count')->default(0);
            $table->unsignedInteger('whatsapp_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->decimal('sales_total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['seller_account_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_daily_entries');
    }
};
