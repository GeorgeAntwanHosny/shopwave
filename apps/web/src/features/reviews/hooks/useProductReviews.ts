import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface ReviewReply {
  reply: string;
  created_at: string;
}

export interface ProductReview {
  id: number;
  user_id: number;
  rating: number;
  comment: string | null;
  created_at: string;
  user: { name: string };
  reply: ReviewReply | null;
}

interface ProductReviewsResponse {
  reviews: ProductReview[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useProductReviews(slug: string) {
  return useQuery({
    queryKey: ["product-reviews", slug],
    queryFn: () => apiFetch<ProductReviewsResponse>(`/api/v1/products/${slug}/reviews`),
    enabled: !!slug,
  });
}