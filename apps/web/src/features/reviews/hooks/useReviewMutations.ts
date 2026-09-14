import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface ReviewPayload {
  rating: number;
  comment?: string;
}

function invalidateReviewRelatedQueries(queryClient: ReturnType<typeof useQueryClient>) {
  queryClient.invalidateQueries({ queryKey: ["order"] });
  queryClient.invalidateQueries({ queryKey: ["orders"] });
  queryClient.invalidateQueries({ queryKey: ["vendor-order"] });
  queryClient.invalidateQueries({ queryKey: ["vendor-orders"] });
  queryClient.invalidateQueries({ queryKey: ["product-reviews"] });
  queryClient.invalidateQueries({ queryKey: ["product-review-eligibility"] });
  queryClient.invalidateQueries({ queryKey: ["product"] });
}

export function useCreateReview(orderItemId: number) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: ReviewPayload) =>
      apiFetch(`/api/v1/order-items/${orderItemId}/reviews`, {
        method: "POST",
        body: JSON.stringify(payload),
        auth: true,
      }),
    onSuccess: () => invalidateReviewRelatedQueries(queryClient),
  });
}

export function useUpdateReview(reviewId: number) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: Partial<ReviewPayload>) =>
      apiFetch(`/api/v1/reviews/${reviewId}`, {
        method: "PUT",
        body: JSON.stringify(payload),
        auth: true,
      }),
    onSuccess: () => invalidateReviewRelatedQueries(queryClient),
  });
}

export function useCreateReviewReply(reviewId: number) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (reply: string) =>
      apiFetch(`/api/v1/reviews/${reviewId}/reply`, {
        method: "POST",
        body: JSON.stringify({ reply }),
        auth: true,
      }),
    onSuccess: () => invalidateReviewRelatedQueries(queryClient),
  });
}