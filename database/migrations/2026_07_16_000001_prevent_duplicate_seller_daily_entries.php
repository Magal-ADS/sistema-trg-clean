<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_daily_entries', function (Blueprint $table) {
            $table->unique(['seller_account_id', 'entry_date'], 'seller_daily_entries_seller_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('seller_daily_entries', function (Blueprint $table) {
            $table->dropUnique('seller_daily_entries_seller_date_unique');
        });
    }
};
