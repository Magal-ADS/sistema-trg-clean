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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->change();
            $table->string('customer_cpf', 20)->nullable()->index()->after('customer_email');
            $table->string('customer_type', 30)->nullable()->after('customer_phone');
            $table->foreignId('city_id')->nullable()->after('customer_type')->constrained('cities')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn(['customer_cpf', 'customer_type']);
            $table->string('customer_email')->nullable(false)->change();
        });
    }
};
