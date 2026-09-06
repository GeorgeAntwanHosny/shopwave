import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface VendorProductFilters {
  q?: string;
  category_id?: string;
  sort?: string;
  is_active?: string; // "1" | "0" | "" (all)
  page?: number;
}

interface VendorProductSummary {
  id: number;
  name: string;
  price: string;
  stock_quantity: number;
  is_active: boolean;
  images: { id: number; url: string }[];
}

interface VendorProductsResponse {
  products: VendorProductSummary[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useVendorProducts(filters: VendorProductFilters = {}) {
  const params = new URLSearchParams(
    Object.entries(filters)
      .filter(([, v]) => v !== undefined && v !== null && v !== "")
      .map(([k, v]) => [k, String(v)])
  ).toString();

  return useQuery({
    queryKey: ["vendor-products", filters],
    queryFn: () =>
      apiFetch<VendorProductsResponse>(`/api/v1/vendor/products${params ? `?${params}` : ""}`, { auth: true }),
  });
}