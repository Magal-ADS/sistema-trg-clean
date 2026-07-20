<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\LaunchAdminAccount;
use App\Models\SellerAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSellerEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_a_sellers_edit_page_when_its_city_is_inactive(): void
    {
        $admin = LaunchAdminAccount::query()->create([
            'name' => 'Administrador',
            'email' => 'admin-teste@example.com',
            'password' => '1234',
            'is_active' => true,
        ]);
        $city = City::query()->create([
            'name' => 'Cidade Inativa',
            'state' => 'SP',
            'is_active' => false,
        ]);
        $seller = SellerAccount::query()->create([
            'name' => 'Vendedor Teste',
            'email' => 'vendedor-teste@example.com',
            'city_id' => $city->id,
            'password' => '1234',
            'is_active' => true,
        ]);

        $response = $this
            ->withSession(['launch_admin_id' => $admin->id])
            ->get(route('launches.admin.sellers.edit', $seller));

        $response
            ->assertOk()
            ->assertSee('Editar vendedor')
            ->assertSee('Cidade Inativa - SP');
    }
}
