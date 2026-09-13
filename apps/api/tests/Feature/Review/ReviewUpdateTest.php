<?php

namespace Tests\Feature\Review;

use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewUpdateTest extends TestCase
{
    public function test_owner_can_update_within_the_edit_window(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'rating' => 3, 'created_at' => now()->subHours(10)]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/reviews/{$review->id}", ['rating' => 5]);

        $response->assertStatus(200)->assertJsonPath('data.rating', 5);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5]);
    }

    public function test_owner_cannot_update_after_the_edit_window(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'created_at' => now()->subHours(50)]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/reviews/{$review->id}", ['rating' => 1]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_update_the_review(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($other);

        $response = $this->putJson("/api/v1/reviews/{$review->id}", ['rating' => 1]);

        $response->assertStatus(403);
    }
}
