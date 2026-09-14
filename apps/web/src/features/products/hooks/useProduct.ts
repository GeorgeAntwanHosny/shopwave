import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface ProductDetail {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  price: string;
  stock_quantity: number;
  average_rating: string;
  rating_count: number;
  images: { id: number; url: string }[];
  vendor: { id: number; shop_name: string };
}

export function useProduct(slug: string) {
  return useQuery({
    queryKey: ["product", slug],
    queryFn: () => apiFetch<ProductDetail>(`/api/v1/products/${slug}`),
    enabled: !!slug,
  });
}