"use client";

import { use, useState } from "react";
import Image from "next/image";
import { useProduct } from "@/features/products/hooks/useProduct";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";

export default function ProductDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  const { data: product, isLoading, isError } = useProduct(slug);
  const [imageFailed, setImageFailed] = useState(false);

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

  const image = product.images[0]?.url;
  const showImage = Boolean(image) && !imageFailed;

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
        <p className="text-3xl font-bold text-foreground">${product.price}</p>
        {product.stock_quantity > 0 ? (
          <Badge variant="default">In stock</Badge>
        ) : (
          <Badge variant="secondary">Out of stock</Badge>
        )}
        {product.description && <p className="whitespace-pre-line text-muted-foreground">{product.description}</p>}
      </div>
    </div>
  );
}