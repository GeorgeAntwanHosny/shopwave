"use client";

import { Skeleton } from "@/components/ui/skeleton";
import { StatCard } from "@/features/vendor/dashboard/components/stat-card";
import { useAdminStats } from "@/features/admin/hooks/useAdminStats";
import { useAdminRevenueChart, useAdminOrdersChart } from "@/features/admin/hooks/useAdminCharts";
import { AdminRevenueChart } from "@/features/admin/components/admin-revenue-chart";
import { AdminOrdersChart } from "@/features/admin/components/admin-orders-chart";

export default function AdminDashboardPage() {
  const { data: stats, isLoading, isError } = useAdminStats();
  const { data: revenueData, isLoading: revenueLoading } = useAdminRevenueChart();
  const { data: ordersData, isLoading: ordersLoading } = useAdminOrdersChart();

  return (
    <div className="mx-auto max-w-4xl space-y-8 p-4 sm:p-6 lg:p-8">
      <h1 className="text-2xl font-semibold text-foreground">Platform overview</h1>

      {isLoading ? (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
          {Array.from({ length: 7 }).map((_, i) => <Skeleton key={i} className="h-20 w-full" />)}
        </div>
      ) : isError || !stats ? (
        <p className="text-destructive">Could not load platform stats.</p>
      ) : (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <StatCard label="GMV" value={`$${stats.gmv}`} hint="Gross merchandise value" />
          <StatCard label="Platform revenue" value={`$${stats.platform_revenue}`} />
          <StatCard label="Orders" value={String(stats.orders_count)} />
          <StatCard label="Pending payouts" value={String(stats.pending_payouts_count)} />
          <StatCard label="Vendors" value={String(stats.vendors_count)} />
          <StatCard label="Active products" value={String(stats.active_products_count)} />
          <StatCard label="Flagged products" value={String(stats.flagged_products_count)} />
        </div>
      )}

      <div>
        <h2 className="mb-3 text-lg font-semibold text-foreground">Revenue, last 30 days</h2>
        {revenueLoading ? <Skeleton className="h-72 w-full" /> : revenueData ? <AdminRevenueChart data={revenueData} /> : null}
      </div>

      <div>
        <h2 className="mb-3 text-lg font-semibold text-foreground">Orders, last 30 days</h2>
        {ordersLoading ? <Skeleton className="h-72 w-full" /> : ordersData ? <AdminOrdersChart data={ordersData} /> : null}
      </div>
    </div>
  );
}