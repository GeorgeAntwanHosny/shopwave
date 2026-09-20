import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface AdminProduct {
  id: number;
  name: string;
  price: string;
  is_active: boolean;
  is_flagged: boolean;
  flagged_reason: string | null;
  vendor: { id: number; shop_name: string };
}

interface AdminProductsResponse {
  products: AdminProduct[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useAdminProducts(filters: { q?: string; flagged?: string; page?: number }) {
  const params = new URLSearchParams(
    Object.entries(filters).filter(([, v]) => v) as [string, string][]
  ).toString();

  return useQuery({
    queryKey: ["admin-products", filters],
    queryFn: () => apiFetch<AdminProductsResponse>(`/api/v1/admin/products${params ? `?${params}` : ""}`, { auth: true }),
  });
}

export function useFlagProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason: string }) =>
      apiFetch(`/api/v1/admin/products/${id}/flag`, { method: "POST", body: JSON.stringify({ reason }), auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["admin-products"] }),
  });
}

export function useUnflagProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/admin/products/${id}/unflag`, { method: "POST", auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["admin-products"] }),
  });
}

export function useDeactivateProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/admin/products/${id}/deactivate`, { method: "POST", auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["admin-products"] }),
  });
}