"use client";

import { useState } from "react";
import { MoreVertical } from "lucide-react";
import { StarRating } from "@/components/star-rating";
import { Skeleton } from "@/components/ui/skeleton";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { useProductReviews } from "@/features/reviews/hooks/useProductReviews";
import { useMe } from "@/features/auth/hooks/useMe";
import { ReplyForm } from "@/features/reviews/components/reply-form";
import { WriteReviewForm } from "@/features/reviews/components/write-review-form";

const EDIT_WINDOW_HOURS = 48;

interface ReviewListProps {
  slug: string;
  /** True when the current logged-in user is the vendor who owns this
   *  product — lets them reply inline. */
  canReply?: boolean;
}

export function ReviewList({ slug, canReply = false }: ReviewListProps) {
  const { data, isLoading, isError } = useProductReviews(slug);
  const { data: me } = useMe();
  // Tracks which single review card is currently swapped into edit mode —
  // only ever one at a time, and only ever the viewer's own review.
  const [editingReviewId, setEditingReviewId] = useState<number | null>(null);

  if (isLoading) {
    return (
      <div className="space-y-3">
        {Array.from({ length: 2 }).map((_, i) => <Skeleton key={i} className="h-20 w-full" />)}
      </div>
    );
  }

  if (isError) {
    return <p className="text-sm text-destructive">Could not load reviews.</p>;
  }

  if (!data || data.reviews.length === 0) {
    return <p className="text-sm text-muted-foreground">No reviews yet.</p>;
  }

  return (
    <div className="space-y-4">
      {data.reviews.map((review) => {
        const isOwnReview = me?.user.id === review.user_id;
        const isWithinEditWindow =
          new Date().getTime() - new Date(review.created_at).getTime() < EDIT_WINDOW_HOURS * 3600 * 1000;
        const isEditing = editingReviewId === review.id;

        if (isEditing) {
          return (
            <div key={review.id} className="rounded-lg border-2 border-primary bg-card p-4">
              <p className="mb-2 text-sm font-medium text-foreground">Edit your review</p>
              <WriteReviewForm
                orderItemId={0}
                existingReview={{ id: review.id, rating: review.rating, comment: review.comment }}
                onDone={() => setEditingReviewId(null)}
              />
            </div>
          );
        }

        return (
          <div
            key={review.id}
            className={
              isOwnReview
                ? "rounded-lg border-2 border-primary bg-card p-4"
                : "rounded-lg border border-border bg-card p-4"
            }
          >
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <p className="text-sm font-medium text-card-foreground">{review.user.name}</p>
                {isOwnReview && (
                  <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                    Your review
                  </span>
                )}
              </div>
              <div className="flex items-center gap-2">
                <span className="text-xs text-muted-foreground">
                  {new Date(review.created_at).toLocaleDateString()}
                </span>
                {isOwnReview && isWithinEditWindow && (
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <button
                        className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                        aria-label="Review options"
                      >
                        <MoreVertical className="h-4 w-4" />
                      </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem onClick={() => setEditingReviewId(review.id)}>
                        Edit review
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                )}
              </div>
            </div>

            <div className="mt-1">
              <StarRating value={review.rating} size="sm" />
            </div>
            {review.comment && <p className="mt-2 text-sm text-muted-foreground">{review.comment}</p>}

            <ReplyForm
              reviewId={review.id}
              existingReply={review.reply?.reply ?? null}
              canReply={canReply}
              label={canReply ? "Your response" : "Vendor response"}
            />
          </div>
        );
      })}
    </div>
  );
}