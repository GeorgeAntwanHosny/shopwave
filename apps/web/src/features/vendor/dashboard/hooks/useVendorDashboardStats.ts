import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

interface DashboardStats {
  total_revenue: string;
  orders_count: number;
  average_order_value: string;
  pending_payouts_count: number;
  low_stock_count: number;
}

export function useVendorDashboardStats() {
  const token = useAuthStore((s) => s.token);

  return useQuery({
    queryKey: ["vendor-dashboard-stats"],
    queryFn: () => apiFetch<DashboardStats>("/api/v1/vendor/dashboard/stats", { auth: true }),
    enabled: !!token,
  });
}