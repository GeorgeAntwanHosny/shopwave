import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface VendorProductDetail {
  id: number;
  name: string;
  description: string | null;
  price: string;
  stock_quantity: number;
  category_id: number | null;
  is_active: boolean;
  images: { id: number; url: string; sort_order: number }[];
}

export function useVendorProduct(id: string) {
  return useQuery({
    queryKey: ["vendor-product", id],
    queryFn: () => apiFetch<VendorProductDetail>(`/api/v1/vendor/products/${id}`, { auth: true }),
    enabled: !!id,
  });
}