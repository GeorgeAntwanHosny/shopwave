<?php

namespace Tests\Feature\Review;

use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewReplyStoreTest extends TestCase
{
    public function test_vendor_can_reply_to_a_review_of_their_product(): void
    {
        $vendor = Vendor::factory()->create();
        $review = Review::factory()->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson("/api/v1/reviews/{$review->id}/reply", ['reply' => 'Thanks for your feedback!']);

        $response->assertStatus(201)->assertJsonPath('data.reply', 'Thanks for your feedback!');
        $this->assertDatabaseHas('review_replies', ['review_id' => $review->id, 'vendor_id' => $vendor->id, 'reply' => 'Thanks for your feedback!']);
    }

    public function test_non_owning_vendor_cannot_reply(): void
    {
        $owner = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $review = Review::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($other->user);

        $response = $this->postJson("/api/v1/reviews/{$review->id}/reply", ['reply' => 'Hi']);

        $response->assertStatus(403);
    }

    public function test_a_review_cannot_receive_two_replies(): void
    {
        $vendor = Vendor::factory()->create();
        $review = Review::factory()->create(['vendor_id' => $vendor->id]);
        ReviewReply::factory()->create(['review_id' => $review->id, 'vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson("/api/v1/reviews/{$review->id}/reply", ['reply' => 'Second reply']);

        $response->assertStatus(422);
    }
}
