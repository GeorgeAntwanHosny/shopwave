"use client";

import { use } from "react";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { useOrder } from "@/features/orders/hooks/useOrder";

export default function OrderDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const { data: order, isLoading, isError } = useOrder(id);

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
        <p className="text-muted-foreground">{order.vendor.shop_name}</p>
        <Badge className="mt-2">{order.status}</Badge>
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
          <div className="flex justify-between text-base font-semibold text-foreground">
            <span>Total</span>
            <span>${order.total}</span>
          </div>
        </div>
      </div>
    </div>
  );
}