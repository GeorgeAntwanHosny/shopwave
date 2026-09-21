"use client";

import { use, useState } from "react";
import Link from "next/link";
import { toast } from "sonner";
import { ArrowLeft, Loader2 } from "lucide-react";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { useAdminOrder, useRefundOrder, useReleaseOrderFunds } from "@/features/admin/hooks/useAdminOrders";

export default function AdminOrderDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const [reason, setReason] = useState("");
  const { data: order, isLoading, isError } = useAdminOrder(id);
  const { mutate: refund, isPending: isRefunding } = useRefundOrder();
  const { mutate: releaseFunds, isPending: isReleasing } = useReleaseOrderFunds();

  if (isLoading) {
    return <div className="mx-auto max-w-2xl space-y-4 p-4 sm:p-6 lg:p-8"><Skeleton className="h-8 w-48" /><Skeleton className="h-64 w-full" /></div>;
  }
  if (isError || !order) {
    return <div className="mx-auto max-w-2xl p-4 text-center sm:p-6 lg:p-8"><p className="text-destructive">Could not load this order.</p></div>;
  }

  function handleRefund() {
    refund({ id: order!.id, reason }, { onSuccess: () => { toast.success("Order refunded."); setReason(""); }, onError: () => toast.error("Could not refund order.") });
  }
  function handleRelease() {
    releaseFunds(order!.id, { onSuccess: () => toast.success("Funds released to vendor."), onError: () => toast.error("Could not release funds.") });
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6 p-4 sm:p-6 lg:p-8">
      <Link href="/admin/orders" className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <ArrowLeft className="h-4 w-4" />
        Back to orders
      </Link>

      <div>
        <h1 className="text-2xl font-semibold text-foreground">Order #{order.id}</h1>
        <div className="mt-2 flex flex-wrap gap-2">
          <Badge variant={order.status === "paid" ? "default" : "secondary"}>{order.status}</Badge>
          <Badge variant="outline">{order.fulfillment_status}</Badge>
          <Badge variant={order.transferred_at ? "default" : "secondary"}>{order.transferred_at ? "Paid out" : "Pending payout"}</Badge>
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <div className="rounded-lg border border-border bg-card p-4">
          <p className="text-sm font-medium text-foreground">Customer</p>
          <p className="mt-1 text-sm text-muted-foreground">{order.user.name}</p>
          <p className="text-sm text-muted-foreground">{order.user.email}</p>
        </div>
        <div className="rounded-lg border border-border bg-card p-4">
          <p className="text-sm font-medium text-foreground">Vendor</p>
          <Link href={`/admin/vendors/${order.vendor.id}`} className="mt-1 block text-sm text-primary hover:underline">{order.vendor.shop_name}</Link>
        </div>
      </div>

      <div className="rounded-lg border border-border bg-card p-4">
        <p className="mb-3 text-sm font-medium text-foreground">Items</p>
        <div className="space-y-2">
          {order.items.map((item) => (
            <div key={item.id} className="flex justify-between text-sm"><span>{item.quantity}× {item.product_name}</span><span>${item.subtotal}</span></div>
          ))}
        </div>
        <div className="mt-4 space-y-1 border-t border-border pt-4 text-sm">
          <div className="flex justify-between text-muted-foreground"><span>Subtotal</span><span>${order.subtotal}</span></div>
          {Number(order.discount_amount) > 0 && <div className="flex justify-between text-muted-foreground"><span>Discount</span><span>-${order.discount_amount}</span></div>}
          <div className="flex justify-between text-muted-foreground"><span>Platform fee</span><span>-${order.platform_fee_amount}</span></div>
          <div className="flex justify-between text-muted-foreground"><span>Vendor payout</span><span>${order.vendor_payout_amount}</span></div>
          <div className="flex justify-between text-base font-semibold text-foreground"><span>Total</span><span>${order.total}</span></div>
        </div>
      </div>

      {order.status === "paid" && (
        <div className="flex flex-wrap gap-2 border-t border-border pt-4">
          {!order.transferred_at && (
            <Button variant="outline" disabled={isReleasing} onClick={handleRelease}>{isReleasing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}Release funds</Button>
          )}
          <AlertDialog onOpenChange={(open) => !open && setReason("")}>
            <AlertDialogTrigger asChild><Button variant="destructive">Refund</Button></AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Refund order #{order.id}?</AlertDialogTitle>
                <AlertDialogDescription>
                  Refunds ${order.total} to the customer{order.transferred_at ? " and reverses the vendor's payout first" : ""}. The vendor will be notified. This can&apos;t be undone.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <Textarea placeholder="Reason (optional)" value={reason} onChange={(e) => setReason(e.target.value)} />
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction disabled={isRefunding} onClick={handleRefund}>{isRefunding ? <Loader2 className="h-4 w-4 animate-spin" /> : "Refund"}</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </div>
      )}
    </div>
  );
}