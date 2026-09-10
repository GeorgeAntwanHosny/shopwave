import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface VendorOrderFilters {
  status?: string;
  date_from?: string;
  date_to?: string;
  page?: number;
}

export interface VendorOrderSummary {
  id: number;
  user: { name: string; email: string };
  total: string;
  vendor_payout_amount: string;
  status: string;
  transferred_at: string | null;
  created_at: string;
  items: { product_name: string; quantity: number }[];
}

interface VendorOrdersResponse {
  orders: VendorOrderSummary[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useVendorOrders(filters: VendorOrderFilters = {}) {
  const params = new URLSearchParams(
    Object.entries(filters)
      .filter(([, v]) => v !== undefined && v !== null && v !== "")
      .map(([k, v]) => [k, String(v)])
  ).toString();

  return useQuery({
    queryKey: ["vendor-orders", filters],
    queryFn: () => apiFetch<VendorOrdersResponse>(`/api/v1/vendor/orders${params ? `?${params}` : ""}`, { auth: true }),
  });
}