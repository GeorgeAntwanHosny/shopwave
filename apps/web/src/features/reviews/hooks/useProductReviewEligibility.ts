import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

interface ReviewableItem {
  order_item_id: number;
  order_id: number;
  quantity: number;
  purchased_at: string;
}

interface MyReview {
  id: number;
  rating: number;
  comment: string | null;
  created_at: string;
}

interface ReviewEligibilityResponse {
  reviewable_items: ReviewableItem[];
  my_reviews: MyReview[];
}

export function useProductReviewEligibility(slug: string) {
  const token = useAuthStore((s) => s.token);

  return useQuery({
    queryKey: ["product-review-eligibility", slug],
    queryFn: () => apiFetch<ReviewEligibilityResponse>(`/api/v1/products/${slug}/review-eligibility`, { auth: true }),
    enabled: !!slug && !!token,
  });
}