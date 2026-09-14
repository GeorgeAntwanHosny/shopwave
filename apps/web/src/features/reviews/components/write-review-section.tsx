"use client";

import { useState } from "react";
import { WriteReviewForm } from "@/features/reviews/components/write-review-form";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useProductReviewEligibility } from "@/features/reviews/hooks/useProductReviewEligibility";

export function WriteReviewSection({ slug }: { slug: string }) {
  const { data, isLoading } = useProductReviewEligibility(slug);
  const [selectedItemId, setSelectedItemId] = useState<number | null>(null);
  const [showForm, setShowForm] = useState(false);

  if (isLoading || !data || data.reviewable_items.length === 0) return null;

  const { reviewable_items } = data;

  if (!showForm) {
    return (
      <button
        onClick={() => setShowForm(true)}
        className="text-sm font-medium text-primary underline-offset-4 hover:underline"
      >
        You purchased this — write a review
      </button>
    );
  }

  if (reviewable_items.length === 1) {
    return <WriteReviewForm orderItemId={reviewable_items[0].order_item_id} onDone={() => setShowForm(false)} />;
  }

  return (
    <div className="space-y-3">
      <Select
        value={selectedItemId ? String(selectedItemId) : ""}
        onValueChange={(v) => setSelectedItemId(Number(v))}
      >
        <SelectTrigger>
          <SelectValue placeholder="Which purchase are you reviewing?" />
        </SelectTrigger>
        <SelectContent>
          {reviewable_items.map((item) => (
            <SelectItem key={item.order_item_id} value={String(item.order_item_id)}>
              Order #{item.order_id} · {new Date(item.purchased_at).toLocaleDateString()}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
      {selectedItemId && (
        <WriteReviewForm orderItemId={selectedItemId} onDone={() => setShowForm(false)} />
      )}
    </div>
  );
}