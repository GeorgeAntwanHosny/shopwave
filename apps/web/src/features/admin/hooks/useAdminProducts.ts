import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface AdminProduct {
  id: number;
  name: string;
  price: string;
  stock_quantity: number;
  is_active: boolean;
  is_flagged: boolean;
  flagged_reason: string | null;
  vendor: { id: number; shop_name: string };
}
export interface AdminProductDetail extends AdminProduct {
  slug: string;
  description: string | null;
  category: { id: number; name: string } | null;
  images: { id: number; url: string }[];
  units_sold: number;
  orders_count: number;
}

interface AdminProductsResponse {
  products: AdminProduct[];
  meta: { current_page: number; last_page: number; total: number };
}

interface AdminProductFilters {
  q?: string;
  flagged?: string;
  vendor_id?: string;
  page?: number;
}

export function useAdminProducts(filters: AdminProductFilters) {
  const params = new URLSearchParams(
    Object.entries(filters).filter(([, v]) => v) as [string, string][]
  ).toString();

  return useQuery({
    queryKey: ["admin-products", filters],
    queryFn: () => apiFetch<AdminProductsResponse>(`/api/v1/admin/products${params ? `?${params}` : ""}`, { auth: true }),
  });
}

export function useAdminProduct(id: string) {
  return useQuery({
    queryKey: ["admin-product", id],
    queryFn: () => apiFetch<AdminProductDetail>(`/api/v1/admin/products/${id}`, { auth: true }),
    enabled: !!id,
  });
}

function invalidateProduct(queryClient: ReturnType<typeof useQueryClient>, id: number) {
  queryClient.invalidateQueries({ queryKey: ["admin-products"] });
  queryClient.invalidateQueries({ queryKey: ["admin-product", String(id)] });
}

export function useFlagProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason: string }) =>
      apiFetch(`/api/v1/admin/products/${id}/flag`, { method: "POST", body: JSON.stringify({ reason }), auth: true }),
    onSuccess: (_, { id }) => invalidateProduct(queryClient, id),
  });
}

export function useUnflagProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/admin/products/${id}/unflag`, { method: "POST", auth: true }),
    onSuccess: (_, id) => invalidateProduct(queryClient, id),
  });
}

export function useDeactivateProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason?: string }) =>
      apiFetch(`/api/v1/admin/products/${id}/deactivate`, { method: "POST", body: JSON.stringify({ reason }), auth: true }),
    onSuccess: (_, { id }) => invalidateProduct(queryClient, id),
  });
}

export function useActivateProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason?: string }) =>
      apiFetch(`/api/v1/admin/products/${id}/activate`, { method: "POST", body: JSON.stringify({ reason }), auth: true }),
    onSuccess: (_, { id }) => invalidateProduct(queryClient, id),
  });
}