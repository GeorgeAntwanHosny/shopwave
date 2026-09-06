"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";

interface ProductCardProps {
  product: {
    slug: string;
    name: string;
    price: string;
    images: { url: string }[];
    vendor: { shop_name: string };
  };
}

export function ProductCard({ product }: ProductCardProps) {
  const [imageFailed, setImageFailed] = useState(false);
  const image = product.images[0]?.url;
  const showImage = Boolean(image) && !imageFailed;

  return (
    <Link
      href={`/products/${product.slug}`}
      className="group block overflow-hidden rounded-lg border border-border bg-card transition-shadow hover:shadow-md"
    >
      <div className="relative aspect-square w-full bg-muted">
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
      <div className="p-3">
        <p className="truncate text-sm font-medium text-card-foreground">{product.name}</p>
        <p className="text-xs text-muted-foreground">{product.vendor.shop_name}</p>
        <p className="mt-1 font-semibold text-card-foreground">${product.price}</p>
      </div>
    </Link>
  );
}