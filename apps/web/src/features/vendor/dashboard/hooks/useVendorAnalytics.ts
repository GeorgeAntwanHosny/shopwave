import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

export interface ProductAnalyticsRow {
  product_id: number | null;
  product_name: string;
  units_sold: number;
  revenue: string;
}

interface AnalyticsFilters {
  date_from?: string;
  date_to?: string;
}

export function useVendorAnalytics(filters: AnalyticsFilters) {
  const token = useAuthStore((s) => s.token);
  const params = new URLSearchParams(
    Object.entries(filters).filter(([, v]) => v) as [string, string][]
  ).toString();

  return useQuery({
    queryKey: ["vendor-analytics", filters],
    queryFn: () => apiFetch<ProductAnalyticsRow[]>(`/api/v1/vendor/dashboard/analytics${params ? `?${params}` : ""}`, { auth: true }),
    enabled: !!token,
  });
}