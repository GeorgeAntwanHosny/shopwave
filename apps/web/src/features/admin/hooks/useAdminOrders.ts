import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface AdminOrder {
  id: number;
  user: { name: string; email: string };
  vendor: { shop_name: string };
  total: string;
  vendor_payout_amount: string;
  status: string;
  transferred_at: string | null;
  created_at: string;
}

interface AdminOrdersResponse {
  orders: AdminOrder[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useAdminOrders(filters: { status?: string; transferred?: string; page?: number }) {
  const params = new URLSearchParams(
    Object.entries(filters).filter(([, v]) => v !== undefined && v !== "") as [string, string][]
  ).toString();

  return useQuery({
    queryKey: ["admin-orders", filters],
    queryFn: () => apiFetch<AdminOrdersResponse>(`/api/v1/admin/orders${params ? `?${params}` : ""}`, { auth: true }),
  });
}

export function useRefundOrder() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason?: string }) =>
      apiFetch(`/api/v1/admin/orders/${id}/refund`, { method: "POST", body: JSON.stringify({ reason }), auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["admin-orders"] }),
  });
}

export function useReleaseOrderFunds() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/admin/orders/${id}/release-funds`, { method: "POST", auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["admin-orders"] }),
  });
}