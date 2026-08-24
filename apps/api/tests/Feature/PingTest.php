<?php

namespace Tests\Feature;

use Tests\TestCase;

class PingTest extends TestCase
{
    public function test_ping_returns_ok_status(): void
    {
        $response = $this->getJson('/api/v1/ping');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'service', 'timestamp'])
            ->assertJson(['status' => 'ok', 'service' => 'shopwave-api']);
    }

    public function test_ping_rejects_non_get_requests(): void
    {
        // No real business logic to fail here, so the "failure case" is
        // exercising Laravel's own method-not-allowed handling.
        $response = $this->postJson('/api/v1/ping');

        $response->assertStatus(405);
    }
}