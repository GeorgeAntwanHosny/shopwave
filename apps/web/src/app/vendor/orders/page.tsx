"use client";

import { useState } from "react";
import Link from "next/link";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { PaginationControls } from "@/components/pagination-controls";
import { useVendorOrders } from "@/features/vendor/orders/hooks/useVendorOrders";

const STATUS_OPTIONS = [
  { value: "", label: "All statuses" },
  { value: "paid", label: "Paid" },
  { value: "refunded", label: "Refunded" },
];

export default function VendorOrdersPage() {
  const [status, setStatus] = useState("");
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [page, setPage] = useState(1);

  const { data, isLoading, isFetching, isError } = useVendorOrders({
    status, date_from: dateFrom, date_to: dateTo, page,
  });

  const selectedStatusLabel = STATUS_OPTIONS.find((s) => s.value === status)?.label;
  const hasFilters = Boolean(status || dateFrom || dateTo);

  function resetPage() {
    setPage(1);
  }

  function clearFilters() {
    setStatus("");
    setDateFrom("");
    setDateTo("");
    setPage(1);
  }

  return (
    <div className="mx-auto max-w-3xl space-y-6 p-4 sm:p-6 lg:p-8">
      <h1 className="text-2xl font-semibold text-foreground">Received orders</h1>

      <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
        <Select
          value={status || "none"}
          onValueChange={(v) => { setStatus(v === "none" ? "" : v); resetPage(); }}
        >
          <SelectTrigger className="sm:w-40">
            <SelectValue placeholder="Status">{selectedStatusLabel}</SelectValue>
          </SelectTrigger>
          <SelectContent>
            {STATUS_OPTIONS.map((s) => (
              <SelectItem key={s.value || "none"} value={s.value || "none"}>{s.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>

        <Input
          type="date"
          value={dateFrom}
          onChange={(e) => { setDateFrom(e.target.value); resetPage(); }}
          className="sm:w-40"
          aria-label="From date"
        />
        <Input
          type="date"
          value={dateTo}
          onChange={(e) => { setDateTo(e.target.value); resetPage(); }}
          className="sm:w-40"
          aria-label="To date"
        />

        {hasFilters && (
          <Button variant="ghost" size="sm" onClick={clearFilters}>Clear filters</Button>
        )}
      </div>

      {isLoading ? (
        <div className="space-y-3">
          {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-24 w-full" />)}
        </div>
      ) : isError ? (
        <p className="text-destructive">Could not load received orders.</p>
      ) : data?.orders.length === 0 ? (
        <p className="text-muted-foreground">
          {hasFilters ? "No orders match your filters." : "No orders received yet."}
        </p>
      ) : (
        <>
          <div className="space-y-3">
            {data?.orders.map((order) => (
              <Link
                key={order.id}
                href={`/vendor/orders/${order.id}`}
                className="block rounded-lg border border-border bg-card p-4 transition-shadow hover:shadow-md"
              >
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <p className="font-medium text-card-foreground">{order.user.name}</p>
                    <p className="text-sm text-muted-foreground">
                      {order.items.length} item{order.items.length !== 1 ? "s" : ""} ·{" "}
                      {new Date(order.created_at).toLocaleDateString()}
                    </p>
                  </div>
                  <div className="flex flex-col items-end gap-1">
                    <Badge variant={order.status === "paid" ? "default" : "secondary"}>{order.status}</Badge>
                    <Badge variant={order.transferred_at ? "default" : "secondary"} className="text-[10px]">
                      {order.transferred_at ? "Paid out" : "Payout pending"}
                    </Badge>
                  </div>
                </div>
                <p className="mt-2 font-semibold text-card-foreground">Your payout: ${order.vendor_payout_amount}</p>
              </Link>
            ))}
          </div>
          {data && (
            <PaginationControls
              currentPage={data.meta.current_page}
              lastPage={data.meta.last_page}
              onPageChange={setPage}
              disabled={isFetching}
            />
          )}
        </>
      )}
    </div>
  );
}