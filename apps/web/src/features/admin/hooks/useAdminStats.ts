import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface AdminStats {
  gmv: string;
  platform_revenue: string;
  orders_count: number;
  pending_payouts_count: number;
  vendors_count: number;
  active_products_count: number;
  flagged_products_count: number;
}

export function useAdminStats() {
  return useQuery({
    queryKey: ["admin-stats"],
    queryFn: () => apiFetch<AdminStats>("/api/v1/admin/dashboard/stats", { auth: true }),
  });
}