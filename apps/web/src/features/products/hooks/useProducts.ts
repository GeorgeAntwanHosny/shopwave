import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface ProductFilters {
  q?: string;
  category_id?: string;
  sort?: string;
  page?: number;
}

interface ProductSummary {
  id: number;
  name: string;
  slug: string;
  price: string;
  images: { id: number; url: string }[];
  vendor: { id: number; shop_name: string };
}

interface ProductsResponse {
  products: ProductSummary[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useProducts(filters: ProductFilters) {
  const params = new URLSearchParams(
    Object.entries(filters)
      .filter(([, v]) => v !== undefined && v !== null && v !== "")
      .map(([k, v]) => [k, String(v)])
  ).toString();

  return useQuery({
    queryKey: ["products", filters],
    queryFn: () => apiFetch<ProductsResponse>(`/api/v1/products${params ? `?${params}` : ""}`),
  });
}