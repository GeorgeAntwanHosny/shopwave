<?php

namespace Tests\Feature;

use Tests\TestCase;

class PingTest extends TestCase
{
    public function test_ping_returns_ok_status(): void
    {
        $response = $this->getJson('/api/v1/ping');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success', 'message', 'errors',
                'data' => ['status', 'service', 'timestamp'],
            ])
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'shopwave-api');
    }

    public function test_ping_rejects_non_get_requests(): void
    {
        $response = $this->postJson('/api/v1/ping');

        $response->assertStatus(500)->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'message', 'errors', 'data']);
    }
}
