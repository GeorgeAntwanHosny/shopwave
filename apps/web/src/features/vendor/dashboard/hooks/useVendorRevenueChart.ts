import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

export interface RevenuePoint {
  date: string;
  revenue: string;
}

export function useVendorRevenueChart() {
  const token = useAuthStore((s) => s.token);

  return useQuery({
    queryKey: ["vendor-revenue-chart"],
    queryFn: () => apiFetch<RevenuePoint[]>("/api/v1/vendor/dashboard/revenue-chart", { auth: true }),
    enabled: !!token,
  });
}