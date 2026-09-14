"use client";

import { use, useState } from "react";
import Image from "next/image";
import { toast } from "sonner";
import { Minus, Plus } from "lucide-react";
import { useProduct } from "@/features/products/hooks/useProduct";
import { useMe } from "@/features/auth/hooks/useMe";
import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { StarRating } from "@/components/star-rating";
import { ReviewList } from "@/features/reviews/components/review-list";
import { WriteReviewSection } from "@/features/reviews/components/write-review-section";
import { useAddToCart } from "@/features/cart/hooks/useCartMutations";
import { useCartStore } from "@/features/cart/store/useCartStore";
import { ApiError } from "@/lib/api/client";

export default function ProductDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  const { data: product, isLoading, isError } = useProduct(slug);
  const [imageFailed, setImageFailed] = useState(false);
  const [quantity, setQuantity] = useState(1);
  const addToCart = useAddToCart();
  const openCart = useCartStore((s) => s.openCart);
  const token = useAuthStore((s) => s.token);
  const { data: me } = useMe();

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

  const images = product.images ?? [];
  const image = images[0]?.url;
  const showImage = Boolean(image) && !imageFailed;
  const outOfStock = product.stock_quantity <= 0;
  const isOwningVendor = !!me?.user.vendor && me.user.vendor.id === product.vendor.id;

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
    <div className="mx-auto max-w-4xl space-y-10 p-4 sm:p-6 lg:p-8">
      <div className="grid gap-8 sm:grid-cols-2">
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
            <p className="text-sm text-muted-foreground">by {product.vendor.shop_name}</p>
          </div>

          {product.rating_count > 0 && (
            <div className="flex items-center gap-2">
              <StarRating value={Math.round(Number(product.average_rating))} size="sm" />
              <span className="text-sm text-muted-foreground">
                {product.average_rating} ({product.rating_count} review{product.rating_count !== 1 ? "s" : ""})
              </span>
            </div>
          )}

          <p className="text-3xl font-bold text-foreground">${product.price}</p>
          {outOfStock ? <Badge variant="secondary">Out of stock</Badge> : <Badge variant="default">In stock</Badge>}
          {product.description && <p className="whitespace-pre-line text-muted-foreground">{product.description}</p>}

          {!outOfStock && !isOwningVendor && (
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

      <div className="space-y-6">
        <h2 className="text-xl font-semibold text-foreground">Reviews</h2>

        {token && !isOwningVendor && <WriteReviewSection slug={slug} />}

        <ReviewList slug={slug} canReply={isOwningVendor} />
      </div>
    </div>
  );
}