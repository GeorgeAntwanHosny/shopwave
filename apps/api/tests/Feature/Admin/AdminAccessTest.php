<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/admin/dashboard/stats');
       dump($response->getContent());
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(401);
    }
}
