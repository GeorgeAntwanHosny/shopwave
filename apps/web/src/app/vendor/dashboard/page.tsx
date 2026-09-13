"use client";

import { useState } from "react";
import { Skeleton } from "@/components/ui/skeleton";
import { Input } from "@/components/ui/input";
import { useVendorDashboardStats } from "@/features/vendor/dashboard/hooks/useVendorDashboardStats";
import { useVendorRevenueChart } from "@/features/vendor/dashboard/hooks/useVendorRevenueChart";
import { useVendorLowStock } from "@/features/vendor/dashboard/hooks/useVendorLowStock";
import { useVendorAnalytics } from "@/features/vendor/dashboard/hooks/useVendorAnalytics";
import { StatCard } from "@/features/vendor/dashboard/components/stat-card";
import { RevenueChart } from "@/features/vendor/dashboard/components/revenue-chart";
import { LowStockList } from "@/features/vendor/dashboard/components/low-stock-list";

export default function VendorDashboardPage() {
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");

  const { data: stats, isLoading: statsLoading, isError: statsError } = useVendorDashboardStats();
  const { data: chart, isLoading: chartLoading } = useVendorRevenueChart();
  const { data: lowStock, isLoading: lowStockLoading } = useVendorLowStock();
  const { data: analytics, isLoading: analyticsLoading } = useVendorAnalytics({ date_from: dateFrom, date_to: dateTo });

  return (
    <div className="mx-auto max-w-4xl space-y-8 p-4 sm:p-6 lg:p-8">
      <h1 className="text-2xl font-semibold text-foreground">Dashboard</h1>

      {statsLoading ? (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
          {Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-20 w-full" />)}
        </div>
      ) : statsError || !stats ? (
        <p className="text-destructive">Could not load your stats.</p>
      ) : (
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <StatCard label="Total revenue" value={`$${stats.total_revenue}`} hint="Your payout share" />
          <StatCard label="Orders" value={String(stats.orders_count)} />
          <StatCard label="Avg. order value" value={`$${stats.average_order_value}`} />
          <StatCard
            label="Pending payouts"
            value={String(stats.pending_payouts_count)}
            hint={stats.pending_payouts_count > 0 ? "Transfer not yet completed" : undefined}
          />
        </div>
      )}

      <div>
        <h2 className="mb-3 text-lg font-semibold text-foreground">Revenue, last 30 days</h2>
        {chartLoading ? <Skeleton className="h-64 w-full" /> : chart ? <RevenueChart data={chart} /> : null}
      </div>

      <div>
        <h2 className="mb-3 text-lg font-semibold text-foreground">Low stock</h2>
        {lowStockLoading ? <Skeleton className="h-24 w-full" /> : <LowStockList products={lowStock ?? []} />}
      </div>

      <div>
        <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
          <h2 className="text-lg font-semibold text-foreground">Product performance</h2>
          <div className="flex gap-2">
            <Input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="w-40" aria-label="From date" />
            <Input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="w-40" aria-label="To date" />
          </div>
        </div>

        {analyticsLoading ? (
          <Skeleton className="h-40 w-full" />
        ) : analytics && analytics.length > 0 ? (
          <div className="overflow-hidden rounded-lg border border-border">
            <table className="w-full text-sm">
              <thead className="bg-muted">
                <tr>
                  <th className="p-3 text-left font-medium text-muted-foreground">Product</th>
                  <th className="p-3 text-right font-medium text-muted-foreground">Units sold</th>
                  <th className="p-3 text-right font-medium text-muted-foreground">Revenue</th>
                </tr>
              </thead>
              <tbody>
                {analytics.map((row, i) => (
                  <tr key={i} className="border-t border-border">
                    <td className="p-3 text-card-foreground">{row.product_name}</td>
                    <td className="p-3 text-right text-card-foreground">{row.units_sold}</td>
                    <td className="p-3 text-right text-card-foreground">${row.revenue}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <p className="text-sm text-muted-foreground">No sales in this date range yet.</p>
        )}
      </div>
    </div>
  );
}