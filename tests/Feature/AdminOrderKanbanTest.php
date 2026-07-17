<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\LaunchAdminAccount;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderKanbanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_kanban_and_update_any_order_status(): void
    {
        $admin = LaunchAdminAccount::query()->create([
            'name' => 'Administrador',
            'email' => 'admin-kanban@example.com',
            'password' => '1234',
            'is_active' => true,
        ]);
        $city = City::query()->create(['name' => 'Cidade Admin', 'state' => 'SP', 'is_active' => true]);
        $order = Order::query()->create([
            'code' => 'PED-ADMIN-KANBAN',
            'customer_name' => 'Cliente Admin',
            'city_id' => $city->id,
            'status' => 'pending',
            'total' => 150,
        ]);

        $this->withSession(['launch_admin_id' => $admin->id])
            ->get(route('launches.admin.modules.index', ['module' => 'orders', 'view' => 'kanban']))
            ->assertOk()
            ->assertSee('PED-ADMIN-KANBAN')
            ->assertSee('Kanban de pedidos');

        $this->withSession(['launch_admin_id' => $admin->id])
            ->patchJson(route('launches.admin.orders.status', $order), ['status' => 'delivering'])
            ->assertOk()
            ->assertJsonPath('status', 'delivering');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'delivering']);
    }

    public function test_order_status_endpoint_rejects_invalid_status(): void
    {
        $admin = LaunchAdminAccount::query()->create([
            'name' => 'Administrador',
            'email' => 'admin-validacao@example.com',
            'password' => '1234',
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'code' => 'PED-STATUS-INVALIDO',
            'customer_name' => 'Cliente',
            'status' => 'pending',
            'total' => 10,
        ]);

        $response = $this->withSession(['launch_admin_id' => $admin->id])
            ->patchJson(route('launches.admin.orders.status', $order), ['status' => 'invalid']);

        $this->assertSame(422, $response->getStatusCode());

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_guest_cannot_update_order_status(): void
    {
        $order = Order::query()->create([
            'code' => 'PED-ADMIN-BLOQUEADO',
            'customer_name' => 'Cliente',
            'status' => 'pending',
            'total' => 10,
        ]);

        $this->patchJson(route('launches.admin.orders.status', $order), ['status' => 'completed'])
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }
}
