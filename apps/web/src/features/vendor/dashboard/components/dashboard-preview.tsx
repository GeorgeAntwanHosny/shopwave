"use client";

import Link from "next/link";
import { ArrowRight } from "lucide-react";
import { Skeleton } from "@/components/ui/skeleton";
import { Button } from "@/components/ui/button";
import { useVendorDashboardStats } from "@/features/vendor/dashboard/hooks/useVendorDashboardStats";
import { useVendorRevenueChart } from "@/features/vendor/dashboard/hooks/useVendorRevenueChart";
import { StatCard } from "@/features/vendor/dashboard/components/stat-card";
import { RevenueChart } from "@/features/vendor/dashboard/components/revenue-chart";

export function DashboardPreview() {
  const { data: stats, isLoading: statsLoading, isError: statsError } = useVendorDashboardStats();
  const { data: chart, isLoading: chartLoading } = useVendorRevenueChart();

  if (statsLoading || chartLoading) {
    return (
      <div className="space-y-3">
        <div className="grid grid-cols-3 gap-3">
          {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-16 w-full" />)}
        </div>
        <Skeleton className="h-24 w-full" />
      </div>
    );
  }

  if (statsError || !stats || !chart) {
    return <p className="text-sm text-destructive">Could not load your dashboard preview.</p>;
  }

  return (
    <div className="rounded-lg border border-border bg-card p-6 shadow-sm">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold text-card-foreground">Your business, last 30 days</h2>
        <Button render={<Link href="/vendor/dashboard" />} variant="ghost" size="sm">
          Full dashboard
          <ArrowRight className="ml-1 h-4 w-4" />
        </Button>
      </div>

      <div className="mt-4 grid grid-cols-3 gap-3">
        <StatCard label="Revenue" value={`$${stats.total_revenue}`} />
        <StatCard label="Orders" value={String(stats.orders_count)} />
        <StatCard label="Low stock" value={String(stats.low_stock_count)} />
      </div>

      <div className="mt-4">
        <RevenueChart data={chart} heightClassName="h-24" compact />
      </div>
    </div>
  );
}