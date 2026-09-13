<?php

namespace App\Actions\ReviewReply;

use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;

class CreateReviewReplyAction
{
    public function execute(Review $review, User $user, string $reply): ReviewReply
    {
        return ReviewReply::create([
            'review_id' => $review->id,
            'vendor_id' => $user->vendor->id,
            'reply' => $reply,
        ]);
    }
}
