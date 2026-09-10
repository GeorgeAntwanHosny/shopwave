"use client";

import { use } from "react";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { useVendorOrder } from "@/features/vendor/orders/hooks/useVendorOrder";

export default function VendorOrderDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const { data: order, isLoading, isError } = useVendorOrder(id);

  if (isLoading) {
    return (
      <div className="mx-auto max-w-2xl space-y-4 p-4 sm:p-6 lg:p-8">
        <Skeleton className="h-8 w-48" />
        <Skeleton className="h-40 w-full" />
      </div>
    );
  }

  if (isError || !order) {
    return (
      <div className="mx-auto max-w-2xl p-4 text-center sm:p-6 lg:p-8">
        <p className="text-destructive">Could not load this order.</p>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6 p-4 sm:p-6 lg:p-8">
      <div>
        <h1 className="text-2xl font-semibold text-foreground">Order #{order.id}</h1>
        <p className="text-muted-foreground">{order.user.name} · {order.user.email}</p>
        <div className="mt-2 flex gap-2">
          <Badge variant={order.status === "paid" ? "default" : "secondary"}>{order.status}</Badge>
          <Badge variant={order.transferred_at ? "default" : "secondary"}>
            {order.transferred_at ? "Paid out" : "Payout pending"}
          </Badge>
        </div>
      </div>

      <div className="rounded-lg border border-border bg-card p-4">
        <div className="space-y-2">
          {order.items.map((item) => (
            <div key={item.id} className="flex justify-between text-sm">
              <span>{item.quantity}× {item.product_name}</span>
              <span>${item.subtotal}</span>
            </div>
          ))}
        </div>
        <div className="mt-4 space-y-1 border-t border-border pt-4 text-sm">
          <div className="flex justify-between text-muted-foreground">
            <span>Subtotal</span>
            <span>${order.subtotal}</span>
          </div>
          {Number(order.discount_amount) > 0 && (
            <div className="flex justify-between text-muted-foreground">
              <span>Discount</span>
              <span>-${order.discount_amount}</span>
            </div>
          )}
          <div className="flex justify-between text-muted-foreground">
            <span>Platform fee</span>
            <span>-${order.platform_fee_amount}</span>
          </div>
          <div className="flex justify-between text-base font-semibold text-foreground">
            <span>Your payout</span>
            <span>${order.vendor_payout_amount}</span>
          </div>
        </div>
      </div>
    </div>
  );
}