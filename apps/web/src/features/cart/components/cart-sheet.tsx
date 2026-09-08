"use client";

import { useState } from "react";
import Link from "next/link";
import { toast } from "sonner";
import { Loader2, Minus, Plus, X } from "lucide-react";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { Separator } from "@/components/ui/separator";
import { useCart } from "@/features/cart/hooks/useCart";
import {
  useUpdateCartItem, useRemoveCartItem, useApplyCoupon, useRemoveCoupon,
} from "@/features/cart/hooks/useCartMutations";
import { useCartStore } from "@/features/cart/store/useCartStore";
import { ApiError } from "@/lib/api/client";

export function CartSheet() {
  const isOpen = useCartStore((s) => s.isCartOpen);
  const closeCart = useCartStore((s) => s.closeCart);
  const { data, isLoading } = useCart();
  const updateItem = useUpdateCartItem();
  const removeItem = useRemoveCartItem();
  const applyCoupon = useApplyCoupon();
  const removeCoupon = useRemoveCoupon();
  const [couponInputs, setCouponInputs] = useState<Record<number, string>>({});

  function handleQuantityChange(productId: number, quantity: number) {
    if (quantity < 0) return;
    updateItem.mutate(
      { productId, quantity },
      { onError: (error) => toast.error((error as ApiError).message || "Could not update quantity.") }
    );
  }

  function handleRemove(productId: number) {
    removeItem.mutate(productId, {
      onSuccess: () => toast.success("Item removed."),
      onError: (error) => toast.error((error as ApiError).message || "Could not remove item."),
    });
  }

  function handleApplyCoupon(vendorId: number) {
    const code = couponInputs[vendorId];
    if (!code) return;
    applyCoupon.mutate(code, {
      onSuccess: () => {
        toast.success("Coupon applied.");
        setCouponInputs((prev) => ({ ...prev, [vendorId]: "" }));
      },
      onError: (error) => toast.error((error as ApiError).message || "Invalid coupon."),
    });
  }

  function handleRemoveCoupon() {
    removeCoupon.mutate(undefined, {
      onSuccess: () => toast.success("Coupon removed."),
      onError: () => toast.error("Could not remove coupon."),
    });
  }

  const isEmpty = !isLoading && (!data || data.vendors.length === 0);

  return (
    <Sheet open={isOpen} onOpenChange={(open) => !open && closeCart()}>
      <SheetContent side="right" className="flex w-full flex-col sm:max-w-md">
        <SheetHeader>
          <SheetTitle>Your cart</SheetTitle>
        </SheetHeader>

        <div className="flex-1 space-y-6 overflow-y-auto px-1">
          {isLoading ? (
            <div className="space-y-3 p-4">
              <Skeleton className="h-16 w-full" />
              <Skeleton className="h-16 w-full" />
            </div>
          ) : isEmpty ? (
            <p className="p-4 text-center text-sm text-muted-foreground">Your cart is empty.</p>
          ) : (
            data?.vendors.map((group) => (
              <div key={group.vendor_id} className="space-y-3 px-3">
                <p className="text-sm font-medium text-foreground">{group.shop_name}</p>

                {group.items.map((item) => (
                  <div key={item.product_id} className="flex items-center gap-3">
                    <div
                      className="h-14 w-14 shrink-0 rounded bg-muted bg-cover bg-center"
                      style={item.image ? { backgroundImage: `url(${item.image})` } : undefined}
                    />
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-sm font-medium text-foreground">{item.name}</p>
                      <p className="text-xs text-muted-foreground">${item.price}</p>
                      {item.stock_limited && (
                        <p className="text-xs text-destructive">Only {item.available_stock} in stock</p>
                      )}
                      <div className="mt-1 flex items-center gap-2">
                        <Button
                          variant="outline" size="icon" className="h-6 w-6"
                          onClick={() => handleQuantityChange(item.product_id, item.quantity - 1)}
                          disabled={updateItem.isPending}
                        >
                          <Minus className="h-3 w-3" />
                        </Button>
                        <span className="w-6 text-center text-sm">{item.quantity}</span>
                        <Button
                          variant="outline" size="icon" className="h-6 w-6"
                          onClick={() => handleQuantityChange(item.product_id, item.quantity + 1)}
                          disabled={updateItem.isPending}
                        >
                          <Plus className="h-3 w-3" />
                        </Button>
                      </div>
                    </div>
                    <div className="flex flex-col items-end gap-2">
                      <p className="text-sm font-medium text-foreground">${item.subtotal}</p>
                      <button
                        onClick={() => handleRemove(item.product_id)}
                        className="text-muted-foreground hover:text-destructive"
                        aria-label="Remove item"
                      >
                        <X className="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                ))}

                <div className="flex items-center gap-2">
                  <Input
                    placeholder="Coupon code"
                    value={couponInputs[group.vendor_id] ?? ""}
                    onChange={(e) => setCouponInputs((prev) => ({ ...prev, [group.vendor_id]: e.target.value }))}
                    className="h-8 text-sm"
                  />
                  <Button size="sm" variant="outline" onClick={() => handleApplyCoupon(group.vendor_id)} disabled={applyCoupon.isPending}>
                    {applyCoupon.isPending ? <Loader2 className="h-3 w-3 animate-spin" /> : "Apply"}
                  </Button>
                </div>

                {group.coupon && (
                  <div className="flex items-center justify-between rounded bg-muted px-3 py-2 text-sm">
                    <span>
                      Coupon <strong>{group.coupon.code}</strong> applied (-${group.coupon.discount_amount})
                    </span>
                    <button onClick={handleRemoveCoupon} className="text-muted-foreground hover:text-destructive">
                      <X className="h-3 w-3" />
                    </button>
                  </div>
                )}

                <div className="flex justify-between text-sm font-medium text-foreground">
                  <span>Subtotal</span>
                  <span>${group.total_after_discount}</span>
                </div>
                <Separator />
              </div>
            ))
          )}

          {data && data.unavailable_items.length > 0 && (
            <p className="px-3 text-xs text-muted-foreground">
              {data.unavailable_items.length} item(s) in your cart are no longer available and were excluded from your total.
            </p>
          )}
        </div>

        {!isEmpty && (
          <SheetFooter className="flex-col gap-3 sm:flex-col">
            <div className="flex w-full justify-between text-base font-semibold text-foreground">
              <span>Total</span>
              <span>${data?.grand_total}</span>
            </div>
            <Button className="w-full" disabled title="Checkout arrives in Phase 5">
              Checkout (coming soon)
            </Button>
            <Button render={<Link href="/products" />} variant="outline" className="w-full" onClick={closeCart}>
              Continue shopping
            </Button>
          </SheetFooter>
        )}
      </SheetContent>
    </Sheet>
  );
}