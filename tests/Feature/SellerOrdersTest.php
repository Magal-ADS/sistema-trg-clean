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

    public function test_seller_can_update_status_only_for_an_order_from_their_city(): void
    {
        $sellerCity = City::query()->create(['name' => 'Cidade A', 'state' => 'SP', 'is_active' => true]);
        $otherCity = City::query()->create(['name' => 'Cidade B', 'state' => 'SP', 'is_active' => true]);
        $seller = SellerAccount::query()->create([
            'name' => 'Vendedor Kanban',
            'email' => 'kanban-vendedor@example.com',
            'city_id' => $sellerCity->id,
            'password' => '1234',
            'is_active' => true,
        ]);
        $sellerOrder = $this->createOrder($sellerCity->id, 'PED-KANBAN-LOCAL');
        $otherOrder = $this->createOrder($otherCity->id, 'PED-KANBAN-OUTRO');

        $this->withSession(['launch_seller_id' => $seller->id])
            ->patchJson(route('launches.orders.status', $sellerOrder), ['status' => 'preparing'])
            ->assertOk()
            ->assertJsonPath('status', 'preparing');

        $this->assertDatabaseHas('orders', ['id' => $sellerOrder->id, 'status' => 'preparing']);

        $this->withSession(['launch_seller_id' => $seller->id])
            ->patchJson(route('launches.orders.status', $otherOrder), ['status' => 'completed'])
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $otherOrder->id, 'status' => 'confirmed']);
    }

    public function test_seller_can_open_the_orders_kanban(): void
    {
        $city = City::query()->create(['name' => 'Cidade Kanban', 'state' => 'SP', 'is_active' => true]);
        $seller = SellerAccount::query()->create([
            'name' => 'Vendedor Kanban',
            'email' => 'tela-kanban@example.com',
            'city_id' => $city->id,
            'password' => '1234',
            'is_active' => true,
        ]);
        $this->createOrder($city->id, 'PED-NA-TELA-KANBAN');

        $this->withSession(['launch_seller_id' => $seller->id])
            ->get(route('launches.orders.index', ['view' => 'kanban']))
            ->assertOk()
            ->assertSee('PED-NA-TELA-KANBAN')
            ->assertSee('Em separacao');
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
