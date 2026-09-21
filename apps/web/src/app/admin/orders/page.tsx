"use client";

import { useState } from "react";
import Link from "next/link";
import { toast } from "sonner";
import { Loader2 } from "lucide-react";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { PaginationControls } from "@/components/pagination-controls";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { useAdminOrders, useRefundOrder, useReleaseOrderFunds } from "@/features/admin/hooks/useAdminOrders";

const TRANSFER_OPTIONS = [{ value: "", label: "All" }, { value: "0", label: "Pending payout" }, { value: "1", label: "Paid out" }];

export default function AdminOrdersPage() {
  const [transferred, setTransferred] = useState("");
  const [reason, setReason] = useState("");
  const [page, setPage] = useState(1);
  const { data, isLoading, isFetching, isError } = useAdminOrders({ transferred, page });
  const { mutate: refund, isPending: isRefunding, variables: refundVars } = useRefundOrder();
  const { mutate: releaseFunds, isPending: isReleasing, variables: releaseVar } = useReleaseOrderFunds();

  const selectedLabel = TRANSFER_OPTIONS.find((o) => o.value === transferred)?.label;

  function handleRefund(id: number) {
    refund({ id, reason }, { onSuccess: () => { toast.success("Order refunded."); setReason(""); }, onError: () => toast.error("Could not refund order.") });
  }
  function handleRelease(id: number) {
    releaseFunds(id, { onSuccess: () => toast.success("Funds released to vendor."), onError: () => toast.error("Could not release funds.") });
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
      <h1 className="text-2xl font-semibold text-foreground">Orders</h1>

      <Select value={transferred || "none"} onValueChange={(v) => { setTransferred(v === "none" ? "" : v); setPage(1); }}>
        <SelectTrigger className="sm:w-48"><SelectValue placeholder="Filter">{selectedLabel}</SelectValue></SelectTrigger>
        <SelectContent>{TRANSFER_OPTIONS.map((o) => <SelectItem key={o.value || "none"} value={o.value || "none"}>{o.label}</SelectItem>)}</SelectContent>
      </Select>

      {isLoading ? (
        <div className="space-y-3">{Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-24 w-full" />)}</div>
      ) : isError ? (
        <p className="text-destructive">Could not load orders.</p>
      ) : (
        <>
          <div className="space-y-3">
            {data?.orders.map((order) => {
              const isThisRefunding = isRefunding && refundVars?.id === order.id;
              const isThisReleasing = isReleasing && releaseVar === order.id;
              return (
                <div key={order.id} className="rounded-lg border border-border bg-card p-4">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <Link href={`/admin/orders/${order.id}`} className="font-medium text-card-foreground hover:underline">
                        Order #{order.id} — {order.vendor.shop_name}
                      </Link>
                      <p className="text-sm text-muted-foreground">{order.user.name} · {order.user.email}</p>
                      <p className="text-sm text-muted-foreground">${order.total} · payout ${order.vendor_payout_amount} · {new Date(order.created_at).toLocaleDateString()}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant={order.status === "paid" ? "default" : "secondary"}>{order.status}</Badge>
                      <Badge variant={order.transferred_at ? "default" : "secondary"}>{order.transferred_at ? "Paid out" : "Pending payout"}</Badge>
                    </div>
                  </div>
                  <div className="mt-3 flex flex-wrap gap-2">
                    <Button size="sm" variant="outline" render={<Link href={`/admin/orders/${order.id}`} />}>View details</Button>
                    {order.status === "paid" && (
                      <>
                        {!order.transferred_at && (
                          <Button size="sm" variant="outline" disabled={isThisReleasing} onClick={() => handleRelease(order.id)}>
                            {isThisReleasing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}Release funds
                          </Button>
                        )}
                        <AlertDialog onOpenChange={(open) => !open && setReason("")}>
                          <AlertDialogTrigger asChild><Button size="sm" variant="destructive">Refund</Button></AlertDialogTrigger>
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
                              <AlertDialogAction disabled={isThisRefunding} onClick={() => handleRefund(order.id)}>
                                {isThisRefunding ? <Loader2 className="h-4 w-4 animate-spin" /> : "Refund"}
                              </AlertDialogAction>
                            </AlertDialogFooter>
                          </AlertDialogContent>
                        </AlertDialog>
                      </>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
          {data && <PaginationControls currentPage={data.meta.current_page} lastPage={data.meta.last_page} onPageChange={setPage} disabled={isFetching} />}
        </>
      )}
    </div>
  );
}