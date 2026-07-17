<?php

namespace Tests\Feature;

use App\Models\SellerAccount;
use App\Models\SellerDailyEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerVisitNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_save_an_optional_visit_observation(): void
    {
        $seller = SellerAccount::query()->create([
            'name' => 'Vendedor Teste',
            'email' => 'vendedor-teste@example.com',
            'password' => '1234',
            'is_active' => true,
        ]);

        $response = $this
            ->withSession(['launch_seller_id' => $seller->id])
            ->post(route('launches.store'), [
                'entry_date' => '2026-07-17',
                'whatsapp_count' => 2,
                'notes' => 'Visitas realizadas no Centro e no Bairro Industrial.',
            ]);

        $response->assertRedirect(route('launches.index'));
        $this->assertDatabaseHas('seller_daily_entries', [
            'seller_account_id' => $seller->id,
            'entry_date' => '2026-07-17 00:00:00',
            'whatsapp_count' => 2,
            'notes' => 'Visitas realizadas no Centro e no Bairro Industrial.',
        ]);
    }

    public function test_seller_can_update_the_visit_observation(): void
    {
        $seller = SellerAccount::query()->create([
            'name' => 'Vendedor Teste',
            'email' => 'outro-vendedor@example.com',
            'password' => '1234',
            'is_active' => true,
        ]);
        $entry = SellerDailyEntry::query()->create([
            'seller_account_id' => $seller->id,
            'entry_date' => '2026-07-17',
            'whatsapp_count' => 1,
            'notes' => 'Primeiro local.',
        ]);

        $response = $this
            ->withSession(['launch_seller_id' => $seller->id])
            ->put(route('launches.entries.update', $entry), [
                'entry_date' => '2026-07-17',
                'whatsapp_count' => 3,
                'notes' => 'Centro, Distrito Industrial e Zona Norte.',
            ]);

        $response->assertRedirect(route('launches.index'));
        $this->assertDatabaseHas('seller_daily_entries', [
            'id' => $entry->id,
            'whatsapp_count' => 3,
            'notes' => 'Centro, Distrito Industrial e Zona Norte.',
        ]);
    }
}
