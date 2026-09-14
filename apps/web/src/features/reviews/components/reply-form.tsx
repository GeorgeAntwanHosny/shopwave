"use client";

import { useState } from "react";
import { toast } from "sonner";
import { Loader2 } from "lucide-react";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { useCreateReviewReply } from "@/features/reviews/hooks/useReviewMutations";
import { ApiError } from "@/lib/api/client";

interface ReplyFormProps {
  reviewId: number;
  existingReply: string | null;
  /** Whether the current viewer is allowed to post a reply here (the
   *  review's own vendor). Customers viewing a review pass false, so they
   *  see the reply if one exists, but never a "Reply" button. */
  canReply: boolean;
  label?: string;
}

export function ReplyForm({ reviewId, existingReply, canReply, label = "Vendor response" }: ReplyFormProps) {
  const [showForm, setShowForm] = useState(false);
  const [reply, setReply] = useState("");
  const createReply = useCreateReviewReply(reviewId);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    createReply.mutate(reply, {
      onSuccess: () => {
        toast.success("Reply posted.");
        // Closes the form the moment the reply succeeds — the component
        // then renders the read-only "existingReply" branch once the
        // invalidated query refetches, so it stays closed rather than
        // reopening.
        setShowForm(false);
      },
      onError: (error) => toast.error((error as ApiError).message || "Could not post reply."),
    });
  }

  // A review can only ever receive one reply (backend-enforced) — once it
  // exists, it's permanently read-only, never re-editable.
  if (existingReply) {
    return (
      <div className="mt-3 rounded-md bg-muted p-3">
        <p className="text-xs font-medium text-foreground">{label}</p>
        <p className="mt-1 text-sm text-muted-foreground">{existingReply}</p>
      </div>
    );
  }

  if (!canReply) {
    return null;
  }

  if (!showForm) {
    return (
      <Button variant="outline" size="sm" className="mt-3" onClick={() => setShowForm(true)}>
        Reply
      </Button>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="mt-3 space-y-2">
      <Textarea value={reply} onChange={(e) => setReply(e.target.value)} rows={2} placeholder="Write a reply..." />
      <div className="flex gap-2">
        <Button type="submit" size="sm" disabled={createReply.isPending}>
          {createReply.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : "Post reply"}
        </Button>
        <Button type="button" size="sm" variant="ghost" onClick={() => setShowForm(false)}>
          Cancel
        </Button>
      </div>
    </form>
  );
}