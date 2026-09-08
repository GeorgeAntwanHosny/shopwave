import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface VendorCoupon {
  id: number;
  code: string;
  type: "percentage" | "fixed";
  value: string;
  min_order_amount: string | null;
  max_uses: number | null;
  used_count: number;
  expires_at: string | null;
  is_active: boolean;
}

export function useVendorCoupons() {
  return useQuery({
    queryKey: ["vendor-coupons"],
    queryFn: () => apiFetch<VendorCoupon[]>("/api/v1/vendor/coupons", { auth: true }),
  });
}