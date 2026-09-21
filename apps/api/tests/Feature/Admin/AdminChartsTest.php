<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminChartsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_revenue_chart_returns_thirty_days_zero_filled(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Order::factory()->create(['status' => 'paid', 'total' => 100, 'platform_fee_amount' => 10, 'created_at' => now()]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/dashboard/revenue-chart');

        $series = $response->json('data');
        $this->assertCount(30, $series);
        $this->assertEquals('100.00', collect($series)->last()['gmv']);
    }

    public function test_orders_chart_returns_thirty_days_zero_filled(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Order::factory()->create(['created_at' => now()]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/dashboard/orders-chart');

        $series = $response->json('data');
        $this->assertCount(30, $series);
        $this->assertEquals(1, collect($series)->last()['orders_count']);
    }
}
