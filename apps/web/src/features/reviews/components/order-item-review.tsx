"use client";

import { useState } from "react";
import { StarRating } from "@/components/star-rating";
import { Button } from "@/components/ui/button";
import { WriteReviewForm } from "@/features/reviews/components/write-review-form";
import { ReplyForm } from "@/features/reviews/components/reply-form";

interface OrderItemReviewProps {
  orderItemId: number;
  fulfillmentStatus: string;
  review: {
    id: number;
    rating: number;
    comment: string | null;
    created_at: string;
    reply: { reply: string } | null;
  } | null;
}

const EDIT_WINDOW_HOURS = 48;

export function OrderItemReview({ orderItemId, fulfillmentStatus, review }: OrderItemReviewProps) {
  const [showForm, setShowForm] = useState(false);

  if (fulfillmentStatus !== "delivered") {
    return null;
  }

  if (!review && !showForm) {
    return (
      <Button variant="outline" size="sm" onClick={() => setShowForm(true)}>
        Write a review
      </Button>
    );
  }

  if (!review && showForm) {
    return <WriteReviewForm orderItemId={orderItemId} onDone={() => setShowForm(false)} />;
  }

  const isWithinEditWindow =
    review && new Date().getTime() - new Date(review.created_at).getTime() < EDIT_WINDOW_HOURS * 3600 * 1000;

  if (showForm && review) {
    return (
      <WriteReviewForm
        orderItemId={orderItemId}
        existingReview={{ id: review.id, rating: review.rating, comment: review.comment }}
        onDone={() => setShowForm(false)}
      />
    );
  }

  return (
    <div className="space-y-1">
      <StarRating value={review!.rating} size="sm" />
      {review!.comment && <p className="text-sm text-muted-foreground">{review!.comment}</p>}
      {isWithinEditWindow && (
        <Button variant="ghost" size="sm" className="h-auto p-0 text-xs" onClick={() => setShowForm(true)}>
          Edit review
        </Button>
      )}
      {/* Customer view is always read-only for the reply — canReply=false */}
      <ReplyForm reviewId={review!.id} existingReply={review!.reply?.reply ?? null} canReply={false} />
    </div>
  );
}