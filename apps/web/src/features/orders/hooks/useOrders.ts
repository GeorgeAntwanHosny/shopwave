import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface OrderFilters {
  status?: string;
  date_from?: string;
  date_to?: string;
  page?: number;
}

export interface OrderSummary {
  id: number;
  vendor: { shop_name: string };
  total: string;
  status: string;
  created_at: string;
  items: { product_name: string; quantity: number }[];
}

interface OrdersResponse {
  orders: OrderSummary[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useOrders(filters: OrderFilters = {}) {
  const params = new URLSearchParams(
    Object.entries(filters)
      .filter(([, v]) => v !== undefined && v !== null && v !== "")
      .map(([k, v]) => [k, String(v)])
  ).toString();

  return useQuery({
    queryKey: ["orders", filters],
    queryFn: () => apiFetch<OrdersResponse>(`/api/v1/orders${params ? `?${params}` : ""}`, { auth: true }),
  });
}