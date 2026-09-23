"use client";

import { Suspense, useEffect } from "react";
import { useSearchParams } from "next/navigation";
import { useQueryClient } from "@tanstack/react-query";
import Link from "next/link";
import { Loader2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useCheckoutStatus } from "@/features/checkout/hooks/useCheckoutStatus";

function CheckoutSuccessContent() {
  const searchParams = useSearchParams();
  const paymentIntentId = searchParams.get("payment_intent");
  const { data, isLoading } = useCheckoutStatus(paymentIntentId);
  const queryClient = useQueryClient();

  useEffect(() => {
    if (data?.status === "completed") {
      queryClient.invalidateQueries({ queryKey: ["cart"] });
    }
  }, [data?.status, queryClient]);

  if (!paymentIntentId) {
    return (
      <div className="mx-auto max-w-lg p-4 text-center sm:p-6 lg:p-8">
        <p className="text-destructive">Missing payment reference.</p>
      </div>
    );
  }

  if (isLoading || !data || data.status === "pending") {
    return (
      <div className="mx-auto flex max-w-lg flex-col items-center gap-3 p-4 text-center sm:p-6 lg:p-8">
        <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
        <p className="text-muted-foreground">Confirming your order...</p>
      </div>
    );
  }

  if (data.status === "failed") {
    return (
      <div className="mx-auto max-w-lg space-y-4 p-4 text-center sm:p-6 lg:p-8">
        <p className="text-destructive">Your payment did not go through.</p>
        <Button render={<Link href="/checkout" />}>Try again</Button>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-lg space-y-6 p-4 sm:p-6 lg:p-8">
      <div className="text-center">
        <h1 className="text-2xl font-semibold text-foreground">Order confirmed 🎉</h1>
        <p className="text-muted-foreground">A confirmation email is on its way.</p>
      </div>

      <div className="space-y-4">
        {data.orders.map((order) => (
          <div key={order.id} className="rounded-lg border border-border bg-card p-4">
            <p className="font-medium text-card-foreground">{order.vendor.shop_name}</p>
            <ul className="mt-2 space-y-1 text-sm text-muted-foreground">
              {order.items.map((item, i) => (
                <li key={i}>{item.quantity}× {item.product_name}</li>
              ))}
            </ul>
            <p className="mt-2 font-semibold text-card-foreground">Total: ${order.total}</p>
          </div>
        ))}
      </div>

      <Button render={<Link href="/checkout" />} className="w-full">View your orders</Button>
      <Button render={<Link href="/products" />} variant="outline" className="w-full">Continue shopping</Button>
    </div>
  );
}

function CheckoutSuccessFallback() {
  return (
    <div className="mx-auto flex max-w-lg flex-col items-center gap-3 p-4 text-center sm:p-6 lg:p-8">
      <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
      <p className="text-muted-foreground">Loading order details...</p>
    </div>
  );
}

export default function CheckoutSuccessPage() {
  return (
    <Suspense fallback={<CheckoutSuccessFallback />}>
      <CheckoutSuccessContent />
    </Suspense>
  );
}