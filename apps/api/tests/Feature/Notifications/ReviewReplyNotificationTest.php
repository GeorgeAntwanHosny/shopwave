<?php

namespace Tests\Feature\Notifications;

use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\ReviewReplyPostedNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewReplyNotificationTest extends TestCase
{
    public function test_replying_to_a_review_notifies_its_author(): void
    {
        Notification::fake();

        $author = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $this->postJson("/api/v1/reviews/{$review->id}/reply", ['reply' => 'Thanks for your feedback!']);

        Notification::assertSentTo($author, ReviewReplyPostedNotification::class);
    }
}
