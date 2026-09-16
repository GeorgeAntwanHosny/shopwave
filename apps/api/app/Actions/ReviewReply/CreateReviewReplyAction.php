<?php

namespace App\Actions\ReviewReply;

use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use App\Notifications\ReviewReplyPostedNotification;

class CreateReviewReplyAction
{
    public function execute(Review $review, User $user, string $reply): ReviewReply
    {
        $reviewReply = ReviewReply::create([
            'review_id' => $review->id,
            'vendor_id' => $user->vendor->id,
            'reply' => $reply,
        ]);

        $review->user->notify(new ReviewReplyPostedNotification($reviewReply));

        return $reviewReply;
    }
}
