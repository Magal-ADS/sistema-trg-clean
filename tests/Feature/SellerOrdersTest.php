<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Order;
use App\Models\SellerAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_only_sees_orders_from_their_city(): void
    {
        $sellerCity = City::query()->create([
            'name' => 'Cidade do Vendedor',
            'state' => 'SP',
            'is_active' => true,
        ]);
        $otherCity = City::query()->create([
            'name' => 'Outra Cidade',
            'state' => 'SP',
            'is_active' => true,
        ]);
        $seller = SellerAccount::query()->create([
            'name' => 'Vendedor Teste',
            'email' => 'pedidos-vendedor@example.com',
            'city_id' => $sellerCity->id,
            'city' => $sellerCity->name,
            'state' => $sellerCity->state,
            'password' => '1234',
            'is_active' => true,
        ]);

        $this->createOrder($sellerCity->id, 'PED-CIDADE-VENDEDOR');
        $this->createOrder($otherCity->id, 'PED-OUTRA-CIDADE');

        $response = $this
            ->withSession(['launch_seller_id' => $seller->id])
            ->get(route('launches.orders.index'));

        $response
            ->assertOk()
            ->assertSee('PED-CIDADE-VENDEDOR')
            ->assertDontSee('PED-OUTRA-CIDADE');
    }

    public function test_guest_cannot_access_seller_orders(): void
    {
        $this->get(route('launches.orders.index'))
            ->assertRedirect(route('launches.login.form'));
    }

    private function createOrder(int $cityId, string $code): Order
    {
        return Order::query()->create([
            'code' => $code,
            'customer_name' => 'Cliente Teste',
            'city_id' => $cityId,
            'status' => 'confirmed',
            'total' => 100,
        ]);
    }
}
