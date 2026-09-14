"use client";

import { Star } from "lucide-react";

interface StarRatingProps {
  value: number;
  onChange?: (value: number) => void;
  size?: "sm" | "md";
}

export function StarRating({ value, onChange, size = "md" }: StarRatingProps) {
  const interactive = !!onChange;
  const starSize = size === "sm" ? "h-4 w-4" : "h-5 w-5";

  return (
    <div className="flex items-center gap-0.5" role={interactive ? "radiogroup" : undefined}>
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          type="button"
          disabled={!interactive}
          onClick={() => onChange?.(star)}
          className={interactive ? "cursor-pointer" : "cursor-default"}
          aria-label={interactive ? `Rate ${star} star${star > 1 ? "s" : ""}` : undefined}
        >
          <Star
            className={`${starSize} ${star <= value ? "fill-yellow-400 text-yellow-400" : "text-muted-foreground"}`}
          />
        </button>
      ))}
    </div>
  );
}