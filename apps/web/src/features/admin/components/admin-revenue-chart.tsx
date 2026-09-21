"use client";

import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from "recharts";
import type { RevenueChartPoint } from "@/features/admin/hooks/useAdminCharts";

export function AdminRevenueChart({ data }: { data: RevenueChartPoint[] }) {
  const chartData = data.map((point) => ({
    date: new Date(point.date).toLocaleDateString(undefined, { month: "short", day: "numeric" }),
    GMV: Number(point.gmv),
    "Platform revenue": Number(point.platform_revenue),
  }));

  return (
    <div className="h-72 w-full rounded-lg border border-border bg-card p-4">
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={chartData}>
          <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
          <XAxis dataKey="date" tick={{ fontSize: 12 }} interval={4} />
          <YAxis tick={{ fontSize: 12 }} width={55} />
          <Tooltip formatter={(value: number) => `$${value.toFixed(2)}`} />
          <Legend wrapperStyle={{ fontSize: 12 }} />
          <Line type="monotone" dataKey="GMV" stroke="hsl(var(--primary))" strokeWidth={2} dot={false} />
          <Line type="monotone" dataKey="Platform revenue" stroke="#f59e0b" strokeWidth={2} dot={false} />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
}