"use client";

import { useState } from "react";
import { toast } from "sonner";
import { Loader2 } from "lucide-react";
import { StarRating } from "@/components/star-rating";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { useCreateReview, useUpdateReview } from "@/features/reviews/hooks/useReviewMutations";
import { ApiError } from "@/lib/api/client";

interface WriteReviewFormProps {
  orderItemId: number;
  existingReview?: { id: number; rating: number; comment: string | null } | null;
  onDone?: () => void;
}

export function WriteReviewForm({ orderItemId, existingReview, onDone }: WriteReviewFormProps) {
  const [rating, setRating] = useState(existingReview?.rating ?? 0);
  const [comment, setComment] = useState(existingReview?.comment ?? "");
  const createReview = useCreateReview(orderItemId);
  const updateReview = useUpdateReview(existingReview?.id ?? 0);
  const isPending = createReview.isPending || updateReview.isPending;

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (rating === 0) {
      toast.error("Please select a star rating.");
      return;
    }

    const payload = { rating, comment: comment || undefined };
    const mutation = existingReview ? updateReview : createReview;

    mutation.mutate(payload, {
      onSuccess: () => {
        toast.success(existingReview ? "Review updated." : "Review submitted — thanks!");
        onDone?.();
      },
      onError: (error) => {
        const err = error as ApiError;
        const message = err.fieldErrors ? Object.values(err.fieldErrors).flat().join(" ") : err.message;
        toast.error(message || "Could not submit review.");
      },
    });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-3 rounded-md border border-dashed border-border p-3">
      <StarRating value={rating} onChange={setRating} />
      <Textarea
        placeholder="Share your thoughts (optional)"
        value={comment}
        onChange={(e) => setComment(e.target.value)}
        rows={3}
      />
      <Button type="submit" size="sm" disabled={isPending}>
        {isPending ? <><Loader2 className="mr-2 h-4 w-4 animate-spin" />Saving...</> : existingReview ? "Update review" : "Submit review"}
      </Button>
    </form>
  );
}