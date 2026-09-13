"use client";

import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts";
import type { RevenuePoint } from "@/features/vendor/dashboard/hooks/useVendorRevenueChart";

interface RevenueChartProps {
  data: RevenuePoint[];
  heightClassName?: string;
  compact?: boolean;
}

export function RevenueChart({ data, heightClassName = "h-64", compact = false }: RevenueChartProps) {
  const chartData = data.map((point) => ({
    date: new Date(point.date).toLocaleDateString(undefined, { month: "short", day: "numeric" }),
    revenue: Number(point.revenue),
  }));

  return (
    <div className={`w-full rounded-lg border border-border bg-card p-4 ${heightClassName}`}>
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={chartData}>
          {!compact && <CartesianGrid strokeDasharray="3 3" className="stroke-border" />}
          <XAxis dataKey="date" tick={{ fontSize: 12 }} interval={compact ? 6 : 4} hide={compact} />
          <YAxis tick={{ fontSize: 12 }} width={compact ? 0 : 50} hide={compact} />
          <Tooltip formatter={(value: number) => [`$${value.toFixed(2)}`, "Revenue"]} />
          <Line type="monotone" dataKey="revenue" stroke="hsl(var(--primary))" strokeWidth={2} dot={false} />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
}