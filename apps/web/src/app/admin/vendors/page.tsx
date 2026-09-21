"use client";

import { useState } from "react";
import Link from "next/link";
import { toast } from "sonner";
import { Loader2 } from "lucide-react";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { PaginationControls } from "@/components/pagination-controls";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { useAdminVendors, useSuspendVendor, useReactivateVendor } from "@/features/admin/hooks/useAdminVendors";

export default function AdminVendorsPage() {
  const [page, setPage] = useState(1);
  const [reason, setReason] = useState("");
  const { data, isLoading, isFetching, isError } = useAdminVendors(page);
  const { mutate: suspend, isPending: isSuspending, variables: suspendVars } = useSuspendVendor();
  const { mutate: reactivate, isPending: isReactivating, variables: reactivateVar } = useReactivateVendor();

  function handleSuspend(id: number) {
    suspend(
      { id, reason },
      { onSuccess: () => { toast.success("Vendor suspended."); setReason(""); }, onError: () => toast.error("Could not suspend vendor.") }
    );
  }
  function handleReactivate(id: number) {
    reactivate(id, { onSuccess: () => toast.success("Vendor reactivated."), onError: () => toast.error("Could not reactivate vendor.") });
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
      <h1 className="text-2xl font-semibold text-foreground">Vendors</h1>

      {isLoading ? (
        <div className="space-y-3">{Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-20 w-full" />)}</div>
      ) : isError ? (
        <p className="text-destructive">Could not load vendors.</p>
      ) : (
        <>
          <div className="space-y-3">
            {data?.vendors.map((vendor) => {
              const isThisSuspending = isSuspending && suspendVars?.id === vendor.id;
              const isThisReactivating = isReactivating && reactivateVar === vendor.id;
              return (
                <div key={vendor.id} className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border bg-card p-4">
                  <div>
                    <Link href={`/admin/vendors/${vendor.id}`} className="font-medium text-card-foreground hover:underline">
                      {vendor.shop_name}
                    </Link>
                    <p className="text-sm text-muted-foreground">{vendor.products_count} products · {vendor.orders_count} orders</p>
                    {vendor.is_suspended && vendor.suspension_reason && (
                      <p className="mt-1 text-xs text-destructive">Reason: {vendor.suspension_reason}</p>
                    )}
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <Badge variant={vendor.stripe_onboarding_complete ? "default" : "secondary"}>
                      {vendor.stripe_onboarding_complete ? "Onboarded" : "Onboarding"}
                    </Badge>
                    <Badge variant={vendor.is_suspended ? "destructive" : "default"}>{vendor.is_suspended ? "Suspended" : "Active"}</Badge>
                    <Button size="sm" variant="outline" render={<Link href={`/admin/vendors/${vendor.id}`} />}>View details</Button>
                    {vendor.is_suspended ? (
                      <Button size="sm" variant="outline" disabled={isThisReactivating} onClick={() => handleReactivate(vendor.id)}>
                        {isThisReactivating ? <Loader2 className="h-4 w-4 animate-spin" /> : "Reactivate"}
                      </Button>
                    ) : (
                      <AlertDialog onOpenChange={(open) => !open && setReason("")}>
                        <AlertDialogTrigger asChild><Button size="sm" variant="destructive">Suspend</Button></AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Suspend {vendor.shop_name}?</AlertDialogTitle>
                            <AlertDialogDescription>Their products will disappear from the public catalog and they won&apos;t be able to add new ones.</AlertDialogDescription>
                          </AlertDialogHeader>
                          <Textarea placeholder="Reason (optional)" value={reason} onChange={(e) => setReason(e.target.value)} />
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction disabled={isThisSuspending} onClick={() => handleSuspend(vendor.id)}>
                              {isThisSuspending ? <Loader2 className="h-4 w-4 animate-spin" /> : "Suspend"}
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
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