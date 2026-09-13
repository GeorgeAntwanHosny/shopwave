<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Ownership AND the 48-hour edit window — both are genuinely
     * authorization boundaries ("you're not allowed to do this," not "this
     * data is invalid"), so this lives in a Policy rather than validation.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id && $review->isWithinEditWindow();
    }
}
