import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface AdminVendor {
  id: number;
  shop_name: string;
  stripe_onboarding_complete: boolean;
  is_suspended: boolean;
  suspension_reason: string | null;
  products_count: number;
  orders_count: number;
}
export interface AdminVendorDetail extends AdminVendor {
  shop_slug: string;
  average_rating: string;
  rating_count: number;
  user: { name: string; email: string };
}

interface AdminVendorsResponse {
  vendors: AdminVendor[];
  meta: { current_page: number; last_page: number; total: number };
}

export function useAdminVendors(page: number = 1) {
  return useQuery({
    queryKey: ["admin-vendors", page],
    queryFn: () => apiFetch<AdminVendorsResponse>(`/api/v1/admin/vendors?page=${page}`, { auth: true }),
  });
}

export function useAdminVendor(id: string) {
  return useQuery({
    queryKey: ["admin-vendor", id],
    queryFn: () => apiFetch<AdminVendorDetail>(`/api/v1/admin/vendors/${id}`, { auth: true }),
    enabled: !!id,
  });
}

export function useSuspendVendor() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason: string }) =>
      apiFetch(`/api/v1/admin/vendors/${id}/suspend`, { method: "POST", body: JSON.stringify({ reason }), auth: true }),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ["admin-vendors"] });
      queryClient.invalidateQueries({ queryKey: ["admin-vendor", String(id)] });
    },
  });
}

export function useReactivateVendor() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/admin/vendors/${id}/reactivate`, { method: "POST", auth: true }),
    onSuccess: (_, id) => {
      queryClient.invalidateQueries({ queryKey: ["admin-vendors"] });
      queryClient.invalidateQueries({ queryKey: ["admin-vendor", String(id)] });
    },
  });
}