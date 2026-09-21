"use client";

import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts";
import type { OrdersChartPoint } from "@/features/admin/hooks/useAdminCharts";

export function AdminOrdersChart({ data }: { data: OrdersChartPoint[] }) {
  const chartData = data.map((point) => ({
    date: new Date(point.date).toLocaleDateString(undefined, { month: "short", day: "numeric" }),
    orders: point.orders_count,
  }));

  return (
    <div className="h-72 w-full rounded-lg border border-border bg-card p-4">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={chartData}>
          <CartesianGrid strokeDasharray="3 3" className="stroke-border" />
          <XAxis dataKey="date" tick={{ fontSize: 12 }} interval={4} />
          <YAxis tick={{ fontSize: 12 }} width={30} allowDecimals={false} />
          <Tooltip />
          <Bar dataKey="orders" fill="hsl(var(--primary))" radius={[4, 4, 0, 0]} />
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
}