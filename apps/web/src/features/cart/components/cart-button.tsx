"use client";

import { ShoppingCart } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useCart } from "@/features/cart/hooks/useCart";
import { useCartStore } from "@/features/cart/store/useCartStore";

export function CartButton() {
  const { data } = useCart();
  const toggleCart = useCartStore((s) => s.toggleCart);
  const count = data?.item_count ?? 0;

  return (
    <Button variant="outline" size="icon" className="relative shrink-0" onClick={toggleCart} aria-label="Open cart">
      <ShoppingCart className="h-4 w-4" />
      {count > 0 && (
        <span className="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
          {count}
        </span>
      )}
    </Button>
  );
}