import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface AdminOrder {
  id: number;
  user: { name: string; email: string };
  vendor: { id: number; shop_name: string };
  total: string;
  vendor_payout_amount: string;
  status: string;
  transferred_at: string | null;
  created_at: string;
}

export interface AdminOrderDetail {
  id: number;
  user: { name: string; email: string };
  vendor: { id: number; shop_name: string };
  subtotal: string;
  discount_amount: string;
  platform_fee_amount: string;
  vendor_payout_amount: string;
  total: string;
  status: string;
  fulfillment_status: string;
  transferred_at: string | null;
  refunded_at: string | null;
  created_at: string;
  items: { id: number; product_name: string; price: string; quantity: number; subtotal: string }[];
}

interface AdminOrdersResponse {
  orders: AdminOrder[];
  meta: { current_page: number; last_page: number; total: number };
}

interface AdminOrderFilters {
  status?: string;
  transferred?: string;
  vendor_id?: string;
  page?: number;
}

export function useAdminOrders(filters: AdminOrderFilters) {
  const params = new URLSearchParams(
    Object.entries(filters).filter(([, v]) => v !== undefined && v !== "") as [string, string][]
  ).toString();

  return useQuery({
    queryKey: ["admin-orders", filters],
    queryFn: () => apiFetch<AdminOrdersResponse>(`/api/v1/admin/orders${params ? `?${params}` : ""}`, { auth: true }),
  });
}

export function useAdminOrder(id: string) {
  return useQuery({
    queryKey: ["admin-order", id],
    queryFn: () => apiFetch<AdminOrderDetail>(`/api/v1/admin/orders/${id}`, { auth: true }),
    enabled: !!id,
  });
}

function invalidateOrder(queryClient: ReturnType<typeof useQueryClient>, id: number) {
  queryClient.invalidateQueries({ queryKey: ["admin-orders"] });
  queryClient.invalidateQueries({ queryKey: ["admin-order", String(id)] });
}

export function useRefundOrder() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason?: string }) =>
      apiFetch(`/api/v1/admin/orders/${id}/refund`, { method: "POST", body: JSON.stringify({ reason }), auth: true }),
    onSuccess: (_, { id }) => invalidateOrder(queryClient, id),
  });
}

export function useReleaseOrderFunds() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/admin/orders/${id}/release-funds`, { method: "POST", auth: true }),
    onSuccess: (_, id) => invalidateOrder(queryClient, id),
  });
}