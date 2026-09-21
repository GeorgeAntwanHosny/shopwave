"use client";

import { useState } from "react";
import Link from "next/link";
import { toast } from "sonner";
import { Loader2 } from "lucide-react";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { PaginationControls } from "@/components/pagination-controls";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { useDebouncedValue } from "@/lib/hooks/useDebouncedValue";
import { useAdminProducts, useFlagProduct, useUnflagProduct, useDeactivateProduct, useActivateProduct } from "@/features/admin/hooks/useAdminProducts";

const FLAG_OPTIONS = [{ value: "", label: "All products" }, { value: "1", label: "Flagged only" }];

export default function AdminProductsPage() {
  const [qInput, setQInput] = useState("");
  const [flagged, setFlagged] = useState("");
  const [reason, setReason] = useState("");
  const [page, setPage] = useState(1);
  const q = useDebouncedValue(qInput, 400);

  const { data, isLoading, isFetching, isError } = useAdminProducts({ q, flagged, page });
  const { mutate: flag, isPending: isFlagging, variables: flagVars } = useFlagProduct();
  const { mutate: unflag, isPending: isUnflagging, variables: unflagVar } = useUnflagProduct();
  const { mutate: deactivate, isPending: isDeactivating, variables: deactivateVars } = useDeactivateProduct();
  const { mutate: activate, isPending: isActivating, variables: activateVars } = useActivateProduct();

  const selectedLabel = FLAG_OPTIONS.find((o) => o.value === flagged)?.label;

  function handleFlag(id: number) {
    flag({ id, reason }, { onSuccess: () => { toast.success("Product flagged."); setReason(""); }, onError: () => toast.error("Could not flag product.") });
  }
  function handleUnflag(id: number) {
    unflag(id, { onSuccess: () => toast.success("Product unflagged."), onError: () => toast.error("Could not unflag.") });
  }
  function handleDeactivate(id: number) {
    deactivate({ id }, { onSuccess: () => toast.success("Product deactivated."), onError: () => toast.error("Could not deactivate.") });
  }
  function handleActivate(id: number) {
    activate({ id }, { onSuccess: () => toast.success("Product reactivated."), onError: () => toast.error("Could not reactivate.") });
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
      <h1 className="text-2xl font-semibold text-foreground">Products</h1>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <Input placeholder="Search products..." value={qInput} onChange={(e) => { setQInput(e.target.value); setPage(1); }} className="sm:max-w-xs" />
        <Select value={flagged || "none"} onValueChange={(v) => { setFlagged(v === "none" ? "" : v); setPage(1); }}>
          <SelectTrigger className="sm:w-44"><SelectValue placeholder="Filter">{selectedLabel}</SelectValue></SelectTrigger>
          <SelectContent>{FLAG_OPTIONS.map((o) => <SelectItem key={o.value || "none"} value={o.value || "none"}>{o.label}</SelectItem>)}</SelectContent>
        </Select>
      </div>

      {isLoading ? (
        <div className="space-y-3">{Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-20 w-full" />)}</div>
      ) : isError ? (
        <p className="text-destructive">Could not load products.</p>
      ) : data?.products.length === 0 ? (
        <p className="text-muted-foreground">No products match your filters.</p>
      ) : (
        <>
          <div className="space-y-3">
            {data?.products.map((product) => {
              const isThisFlagging = isFlagging && flagVars?.id === product.id;
              const isThisUnflagging = isUnflagging && unflagVar === product.id;
              const isThisDeactivating = isDeactivating && deactivateVars?.id === product.id;
              const isThisActivating = isActivating && activateVars?.id === product.id;
              return (
                <div key={product.id} className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border bg-card p-4">
                  <div>
                    <Link href={`/admin/products/${product.id}`} className="font-medium text-card-foreground hover:underline">{product.name}</Link>
                    <p className="text-sm text-muted-foreground">{product.vendor.shop_name} · ${product.price} · {product.stock_quantity} in stock</p>
                    {product.is_flagged && product.flagged_reason && <p className="mt-1 text-xs text-destructive">Flagged: {product.flagged_reason}</p>}
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <Badge variant={product.is_active ? "default" : "secondary"}>{product.is_active ? "Active" : "Inactive"}</Badge>
                    {product.is_flagged && <Badge variant="destructive">Flagged</Badge>}
                    <Button size="sm" variant="outline" render={<Link href={`/admin/products/${product.id}`} />}>View details</Button>
                    {product.is_flagged ? (
                      <Button size="sm" variant="outline" disabled={isThisUnflagging} onClick={() => handleUnflag(product.id)}>
                        {isThisUnflagging ? <Loader2 className="h-4 w-4 animate-spin" /> : "Unflag"}
                      </Button>
                    ) : (
                      <AlertDialog onOpenChange={(open) => !open && setReason("")}>
                        <AlertDialogTrigger asChild><Button size="sm" variant="outline">Flag</Button></AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader><AlertDialogTitle>Flag {product.name}?</AlertDialogTitle></AlertDialogHeader>
                          <Textarea placeholder="Reason" value={reason} onChange={(e) => setReason(e.target.value)} />
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction disabled={isThisFlagging || !reason} onClick={() => handleFlag(product.id)}>
                              {isThisFlagging ? <Loader2 className="h-4 w-4 animate-spin" /> : "Flag"}
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    )}
                    {product.is_active ? (
                      <AlertDialog>
                        <AlertDialogTrigger asChild><Button size="sm" variant="destructive">Deactivate</Button></AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Deactivate {product.name}?</AlertDialogTitle>
                            <AlertDialogDescription>This removes it from the public catalog immediately. The vendor will be notified.</AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction disabled={isThisDeactivating} onClick={() => handleDeactivate(product.id)}>
                              {isThisDeactivating ? <Loader2 className="h-4 w-4 animate-spin" /> : "Deactivate"}
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    ) : (
                      <Button size="sm" variant="outline" disabled={isThisActivating} onClick={() => handleActivate(product.id)}>
                        {isThisActivating ? <Loader2 className="h-4 w-4 animate-spin" /> : "Reactivate"}
                      </Button>
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