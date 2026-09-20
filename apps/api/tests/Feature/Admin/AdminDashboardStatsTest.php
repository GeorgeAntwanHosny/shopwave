<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardStatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_it_returns_platform_wide_stats(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Order::factory()->create(['status' => 'paid', 'total' => 100, 'platform_fee_amount' => 10, 'transferred_at' => now()]);
        Order::factory()->create(['status' => 'paid', 'total' => 50, 'platform_fee_amount' => 5, 'transferred_at' => null]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.gmv', '150.00')
            ->assertJsonPath('data.platform_revenue', '15.00')
            ->assertJsonPath('data.pending_payouts_count', 1);
    }
}
