<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['name', 'state']);
        });

        Schema::table('seller_accounts', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('phone')->constrained('cities')->nullOnDelete();
        });

        DB::table('seller_accounts')
            ->select(['city', 'state'])
            ->whereNotNull('city')
            ->where('city', '<>', '')
            ->distinct()
            ->orderBy('city')
            ->get()
            ->each(function (object $sellerCity): void {
                $cityId = DB::table('cities')->insertGetId([
                    'name' => $sellerCity->city,
                    'state' => $sellerCity->state,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('seller_accounts')
                    ->where('city', $sellerCity->city)
                    ->where(function ($query) use ($sellerCity): void {
                        if ($sellerCity->state === null) {
                            $query->whereNull('state');
                        } else {
                            $query->where('state', $sellerCity->state);
                        }
                    })
                    ->update(['city_id' => $cityId]);
            });
    }

    public function down(): void
    {
        Schema::table('seller_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
        });

        Schema::dropIfExists('cities');
    }
};
