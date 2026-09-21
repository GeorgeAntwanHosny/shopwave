"use client";

import { use, useState } from "react";
import Link from "next/link";
import Image from "next/image";
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
import { useAdminProduct, useFlagProduct, useUnflagProduct, useDeactivateProduct, useActivateProduct } from "@/features/admin/hooks/useAdminProducts";

const LOW_STOCK_THRESHOLD = 5;

export default function AdminProductDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const [reason, setReason] = useState("");
  const { data: product, isLoading, isError } = useAdminProduct(id);
  const { mutate: flag, isPending: isFlagging } = useFlagProduct();
  const { mutate: unflag, isPending: isUnflagging } = useUnflagProduct();
  const { mutate: deactivate, isPending: isDeactivating } = useDeactivateProduct();
  const { mutate: activate, isPending: isActivating } = useActivateProduct();

  if (isLoading) {
    return <div className="mx-auto max-w-3xl space-y-4 p-4 sm:p-6 lg:p-8"><Skeleton className="h-8 w-48" /><Skeleton className="h-64 w-full" /></div>;
  }
  if (isError || !product) {
    return <div className="mx-auto max-w-3xl p-4 text-center sm:p-6 lg:p-8"><p className="text-destructive">Could not load this product.</p></div>;
  }

  const image = product.images?.[0]?.url;
  const stockLabel = product.stock_quantity === 0 ? "Out of stock" : product.stock_quantity <= LOW_STOCK_THRESHOLD ? `Low stock (${product.stock_quantity})` : `${product.stock_quantity} in stock`;

  function handleFlag() {
    flag({ id: product!.id, reason }, { onSuccess: () => { toast.success("Product flagged."); setReason(""); }, onError: () => toast.error("Could not flag product.") });
  }
  function handleUnflag() {
    unflag(product!.id, { onSuccess: () => toast.success("Product unflagged."), onError: () => toast.error("Could not unflag.") });
  }
  function handleDeactivate() {
    deactivate({ id: product!.id }, { onSuccess: () => toast.success("Product deactivated."), onError: () => toast.error("Could not deactivate.") });
  }
  function handleActivate() {
    activate({ id: product!.id }, { onSuccess: () => toast.success("Product reactivated."), onError: () => toast.error("Could not reactivate.") });
  }

  return (
    <div className="mx-auto max-w-3xl space-y-6 p-4 sm:p-6 lg:p-8">
      <Link href="/admin/products" className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <ArrowLeft className="h-4 w-4" />
        Back to products
      </Link>

      <div className="grid gap-6 sm:grid-cols-2">
        <div className="relative aspect-square w-full overflow-hidden rounded-lg bg-muted">
          {image ? <Image src={image} alt={product.name} fill className="object-cover" unoptimized /> : <div className="flex h-full items-center justify-center text-sm text-muted-foreground">No image</div>}
        </div>
        <div className="space-y-3">
          <div>
            <h1 className="text-2xl font-semibold text-foreground">{product.name}</h1>
            <Link href={`/admin/vendors/${product.vendor.id}`} className="text-sm text-muted-foreground hover:underline">{product.vendor.shop_name}</Link>
          </div>
          <p className="text-2xl font-bold text-foreground">${product.price}</p>
          <div className="flex flex-wrap gap-2">
            <Badge variant={product.is_active ? "default" : "secondary"}>{product.is_active ? "Active" : "Inactive"}</Badge>
            {product.is_flagged && <Badge variant="destructive">Flagged</Badge>}
            <Badge variant={product.stock_quantity === 0 ? "destructive" : product.stock_quantity <= LOW_STOCK_THRESHOLD ? "secondary" : "default"}>{stockLabel}</Badge>
          </div>
          {product.category && <p className="text-sm text-muted-foreground">Category: {product.category.name}</p>}
          {product.description && <p className="whitespace-pre-line text-sm text-muted-foreground">{product.description}</p>}
          {product.is_flagged && product.flagged_reason && <p className="text-sm text-destructive">Flagged reason: {product.flagged_reason}</p>}
          <div className="grid grid-cols-2 gap-4 rounded-lg border border-border bg-card p-4 text-sm">
            <div><p className="text-muted-foreground">Units sold</p><p className="font-medium text-foreground">{product.units_sold}</p></div>
            <div><p className="text-muted-foreground">Orders placed</p><p className="font-medium text-foreground">{product.orders_count}</p></div>
          </div>
        </div>
      </div>

      <div className="flex flex-wrap gap-2 border-t border-border pt-4">
        {product.is_flagged ? (
          <Button variant="outline" disabled={isUnflagging} onClick={handleUnflag}>{isUnflagging ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}Unflag</Button>
        ) : (
          <AlertDialog onOpenChange={(open) => !open && setReason("")}>
            <AlertDialogTrigger asChild><Button variant="outline">Flag</Button></AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader><AlertDialogTitle>Flag {product.name}?</AlertDialogTitle></AlertDialogHeader>
              <Textarea placeholder="Reason" value={reason} onChange={(e) => setReason(e.target.value)} />
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction disabled={isFlagging || !reason} onClick={handleFlag}>{isFlagging ? <Loader2 className="h-4 w-4 animate-spin" /> : "Flag"}</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        )}
        {product.is_active ? (
          <AlertDialog>
            <AlertDialogTrigger asChild><Button variant="destructive">Deactivate</Button></AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Deactivate {product.name}?</AlertDialogTitle>
                <AlertDialogDescription>This removes it from the public catalog immediately. The vendor will be notified.</AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction disabled={isDeactivating} onClick={handleDeactivate}>{isDeactivating ? <Loader2 className="h-4 w-4 animate-spin" /> : "Deactivate"}</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        ) : (
          <Button variant="outline" disabled={isActivating} onClick={handleActivate}>{isActivating ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}Reactivate</Button>
        )}
      </div>
    </div>
  );
}