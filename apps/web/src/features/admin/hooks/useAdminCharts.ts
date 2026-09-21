import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface RevenueChartPoint {
  date: string;
  gmv: string;
  platform_revenue: string;
}

export interface OrdersChartPoint {
  date: string;
  orders_count: number;
}

export function useAdminRevenueChart() {
  return useQuery({
    queryKey: ["admin-revenue-chart"],
    queryFn: () => apiFetch<RevenueChartPoint[]>("/api/v1/admin/dashboard/revenue-chart", { auth: true }),
  });
}

export function useAdminOrdersChart() {
  return useQuery({
    queryKey: ["admin-orders-chart"],
    queryFn: () => apiFetch<OrdersChartPoint[]>("/api/v1/admin/dashboard/orders-chart", { auth: true }),
  });
}