"use client";

import { use } from "react";
import Link from "next/link";
import { toast } from "sonner";
import { ArrowLeft, ExternalLink } from "lucide-react";
import { Skeleton } from "@/components/ui/skeleton";
import { Button } from "@/components/ui/button";
import { ProductForm } from "@/features/vendor/products/components/product-form";
import { ProductImageManager } from "@/features/vendor/products/components/product-image-manager";
import { useVendorProduct } from "@/features/vendor/products/hooks/useVendorProduct";
import { useUpdateProduct } from "@/features/vendor/products/hooks/useUpdateProduct";
import { ApiError } from "@/lib/api/client";

export default function EditProductPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const { data: product, isLoading, isError } = useVendorProduct(id);
  const { mutate, isPending } = useUpdateProduct(id);

  const backLink = (
    <Link
      href="/vendor/products"
      className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
    >
      <ArrowLeft className="h-4 w-4" />
      Back to products
    </Link>
  );

  if (isLoading) {
    return (
      <div className="mx-auto max-w-xl space-y-4 p-4 sm:p-6 lg:p-8">
        {backLink}
        <Skeleton className="h-8 w-48" />
        <Skeleton className="h-64 w-full" />
      </div>
    );
  }

  if (isError || !product) {
    return (
      <div className="mx-auto max-w-xl space-y-4 p-4 text-center sm:p-6 lg:p-8">
        {backLink}
        <p className="text-destructive">Could not load this product.</p>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-xl space-y-8 p-4 sm:p-6 lg:p-8">
      {backLink}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold text-foreground">Edit product</h1>
        <Button render={<Link href={`/products/${product.slug}`} target="_blank" />} variant="outline" size="sm">
          View & reply to reviews
          <ExternalLink className="ml-2 h-4 w-4" />
        </Button>
      </div>

      <ProductForm
        isPending={isPending}
        submitLabel="Save changes"
        defaultValues={{
          name: product.name,
          description: product.description ?? "",
          price: Number(product.price),
          stock_quantity: product.stock_quantity,
          category_id: product.category_id ? String(product.category_id) : "",
          is_active: product.is_active,
        }}
        onSubmit={(values) => {
          mutate(values, {
            onSuccess: () => toast.success("Product updated."),
            onError: (error) => toast.error((error as ApiError).message || "Could not update product."),
          });
        }}
      />

      <ProductImageManager productId={id} images={product.images ?? []} />
    </div>
  );
}