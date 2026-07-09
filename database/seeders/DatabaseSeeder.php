<?php

namespace Database\Seeders;

use App\Models\LaunchAdminAccount;
use App\Models\SellerAccount;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ],
        );

        LaunchAdminAccount::query()->updateOrCreate(
            ['email' => 'admin@weagles.com'],
            [
                'name' => 'Admin',
                'password' => '123',
                'is_active' => true,
            ],
        );

        SellerAccount::query()->updateOrCreate(
            ['email' => 'vendedora@weagles.com'],
            [
                'name' => 'Vendedora Teste',
                'phone' => null,
                'city' => 'Teste',
                'state' => 'SP',
                'password' => '1234',
                'is_active' => true,
            ],
        );
    }
}
