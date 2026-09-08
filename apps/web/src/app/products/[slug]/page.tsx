"use client";

import { use, useState } from "react";
import Image from "next/image";
import { toast } from "sonner";
import { Minus, Plus } from "lucide-react";
import { useProduct } from "@/features/products/hooks/useProduct";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { useAddToCart } from "@/features/cart/hooks/useCartMutations";
import { useCartStore } from "@/features/cart/store/useCartStore";
import { ApiError } from "@/lib/api/client";

export default function ProductDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  const { data: response, isLoading, isError } = useProduct(slug);
  const [imageFailed, setImageFailed] = useState(false);
  const [quantity, setQuantity] = useState(1);
  const addToCart = useAddToCart();
  const openCart = useCartStore((s) => s.openCart);

  // Unwrap the 'data' object from your Laravel ApiResponse::success()
  const product = response?.data ?? response;

  if (isLoading) {
    return (
      <div className="mx-auto max-w-4xl space-y-4 p-4 sm:p-6 lg:p-8">
        <Skeleton className="aspect-square w-full max-w-md" />
        <Skeleton className="h-8 w-64" />
        <Skeleton className="h-4 w-40" />
      </div>
    );
  }

  if (isError || !product) {
    return (
      <div className="mx-auto max-w-4xl p-4 text-center sm:p-6 lg:p-8">
        <p className="text-destructive">Product not found.</p>
      </div>
    );
  }

  // Safely extract properties
  const images = product.images ?? [];
  const image = images[0]?.url;
  const showImage = Boolean(image) && !imageFailed;
  const outOfStock = (product.stock_quantity ?? 0) <= 0;
  const vendorName = product.vendor?.shop_name || "Unknown vendor";

  function handleAddToCart() {
    addToCart.mutate(
      { product_id: product.id, quantity },
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
    <div className="mx-auto grid max-w-4xl gap-8 p-4 sm:grid-cols-2 sm:p-6 lg:p-8">
      <div className="relative aspect-square w-full overflow-hidden rounded-lg bg-muted">
        {showImage ? (
          <Image
            src={image}
            alt={product.name}
            fill
            className="object-cover"
            unoptimized
            loading="eager"
            onError={() => setImageFailed(true)}
          />
        ) : (
          <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
            {image ? "Image failed to load" : "No image"}
          </div>
        )}
      </div>

      <div className="space-y-4">
        <div>
          <h1 className="text-2xl font-semibold text-foreground">{product.name}</h1>
          <p className="text-sm text-muted-foreground">by {vendorName}</p>
        </div>
        <p className="text-3xl font-bold text-foreground">
          {product.price != null ? `$${product.price}` : "Price not available"}
        </p>
        {outOfStock ? <Badge variant="secondary">Out of stock</Badge> : <Badge variant="default">In stock</Badge>}
        {product.description && <p className="whitespace-pre-line text-muted-foreground">{product.description}</p>}

        {!outOfStock && (
          <div className="flex items-center gap-3 pt-2">
            <div className="flex items-center gap-2">
              <Button variant="outline" size="icon" onClick={() => setQuantity((q) => Math.max(1, q - 1))}>
                <Minus className="h-4 w-4" />
              </Button>
              <span className="w-8 text-center">{quantity}</span>
              <Button variant="outline" size="icon" onClick={() => setQuantity((q) => Math.min(product.stock_quantity, q + 1))}>
                <Plus className="h-4 w-4" />
              </Button>
            </div>
            <Button onClick={handleAddToCart} disabled={addToCart.isPending} className="flex-1">
              {addToCart.isPending ? "Adding..." : "Add to cart"}
            </Button>
          </div>
        )}
      </div>
    </div>
  );
}