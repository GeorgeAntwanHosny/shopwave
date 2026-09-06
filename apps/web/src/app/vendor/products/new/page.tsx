"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { ArrowLeft } from "lucide-react";
import { ProductForm } from "@/features/vendor/products/components/product-form";
import { useCreateProduct } from "@/features/vendor/products/hooks/useCreateProduct";
import { ApiError } from "@/lib/api/client";

export default function NewProductPage() {
  const router = useRouter();
  const { mutate, isPending } = useCreateProduct();

  return (
    <div className="mx-auto max-w-xl space-y-6 p-4 sm:p-6 lg:p-8">
      <Link
        href="/vendor/products"
        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
      >
        <ArrowLeft className="h-4 w-4" />
        Back to products
      </Link>

      <h1 className="text-2xl font-semibold text-foreground">Add a product</h1>
      <ProductForm
        isPending={isPending}
        submitLabel="Create product"
        onSubmit={(values) => {
          mutate(values, {
            onSuccess: (data) => {
              toast.success("Product created.");
              router.push(`/vendor/products/${data.id}/edit`);
            },
            onError: (error) => toast.error((error as ApiError).message || "Could not create product."),
          });
        }}
      />
    </div>
  );
}