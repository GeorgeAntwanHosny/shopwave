"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { toast } from "sonner";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useAddToCart } from "@/features/cart/hooks/useCartMutations";
import { useCartStore } from "@/features/cart/store/useCartStore";
import { ApiError } from "@/lib/api/client";

interface ProductCardProps {
  product: {
    id: number;
    slug: string;
    name: string;
    price: string;
    images: { url: string }[];
    vendor: { shop_name: string };
  };
}

export function ProductCard({ product }: ProductCardProps) {
  const [imageFailed, setImageFailed] = useState(false);
  const addToCart = useAddToCart();
  const openCart = useCartStore((s) => s.openCart);
  // Defensive: same reasoning as the detail page — never assume `images`
  // is present, even though the API is expected to always send it.
  const image = product.images?.[0]?.url;
  const showImage = Boolean(image) && !imageFailed;

  function handleQuickAdd(e: React.MouseEvent) {
    e.preventDefault();
    e.stopPropagation();
    addToCart.mutate(
      { product_id: product.id, quantity: 1 },
      {
        onSuccess: () => {
          toast.success(`${product.name} added to cart.`);
          openCart();
        },
        onError: (error) => toast.error((error as ApiError).message || "Could not add to cart."),
      }
    );
  }

  return (
    <Link
      href={`/products/${product.slug}`}
      className="group block overflow-hidden rounded-lg border border-border bg-card transition-shadow hover:shadow-md"
    >
      <div className="relative aspect-square w-full bg-muted">
        {showImage ? (
          <Image
            src={image!}
            alt={product.name}
            fill
            className="object-cover"
            unoptimized
            onError={() => setImageFailed(true)}
          />
        ) : (
          <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
            {image ? "Image failed to load" : "No image"}
          </div>
        )}
        <Button
          size="icon"
          className="absolute bottom-2 right-2 h-8 w-8 opacity-0 shadow transition-opacity group-hover:opacity-100"
          onClick={handleQuickAdd}
          disabled={addToCart.isPending}
          aria-label="Quick add to cart"
        >
          <Plus className="h-4 w-4" />
        </Button>
      </div>
      <div className="p-3">
        <p className="truncate text-sm font-medium text-card-foreground">{product.name}</p>
        <p className="text-xs text-muted-foreground">{product.vendor.shop_name}</p>
        <p className="mt-1 font-semibold text-card-foreground">${product.price}</p>
      </div>
    </Link>
  );
}