"use client";

import { useState } from "react";
import Link from "next/link";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { useVendorProducts } from "@/features/vendor/products/hooks/useVendorProducts";
import { useDeleteProduct } from "@/features/vendor/products/hooks/useDeleteProduct";
import { useCategories } from "@/features/products/hooks/useCategories";
import { flattenCategories } from "@/lib/utils/categories";
import { PRODUCT_SORT_OPTIONS, sortLabel } from "@/lib/constants/product-sort";
import { useDebouncedValue } from "@/lib/hooks/useDebouncedValue";
import { PaginationControls } from "@/components/pagination-controls";

const STATUS_OPTIONS = [
  { value: "", label: "All statuses" },
  { value: "1", label: "Active" },
  { value: "0", label: "Inactive" },
];

export default function VendorProductsPage() {
  const [qInput, setQInput] = useState("");
  const [categoryId, setCategoryId] = useState("");
  const [sort, setSort] = useState("newest");
  const [status, setStatus] = useState("");
  const [page, setPage] = useState(1);
  const q = useDebouncedValue(qInput, 400);

  const { data, isLoading, isFetching, isError } = useVendorProducts({
    q, category_id: categoryId, sort, is_active: status, page,
  });
  const { data: categoryTree, isLoading: categoriesLoading } = useCategories();
  const { mutate: deleteProduct, isPending: isDeleting } = useDeleteProduct();

  const categoryOptions = categoryTree ? flattenCategories(categoryTree) : [];
  const selectedCategoryLabel = categoryOptions.find((c) => String(c.id) === categoryId)?.label;
  const selectedStatusLabel = STATUS_OPTIONS.find((s) => s.value === status)?.label;

  function handleSearchChange(value: string) {
    setQInput(value);
    setPage(1);
  }
  function handleCategoryChange(value: string) {
    setCategoryId(value === "all" ? "" : value);
    setPage(1);
  }
  function handleStatusChange(value: string) {
    setStatus(value === "none" ? "" : value);
    setPage(1);
  }
  function handleSortChange(value: string) {
    setSort(value);
    setPage(1);
  }
  function handlePageChange(nextPage: number) {
    setPage(nextPage);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function handleDelete(id: number) {
    deleteProduct(id, {
      onSuccess: () => toast.success("Product deleted."),
      onError: () => toast.error("Could not delete product."),
    });
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold text-foreground">Your products</h1>
        <Button render={<Link href="/vendor/products/new" />}>Add product</Button>
      </div>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <Input
          placeholder="Search your products..."
          value={qInput}
          onChange={(e) => handleSearchChange(e.target.value)}
          className="sm:max-w-xs"
        />

        {categoriesLoading ? (
          <Skeleton className="h-9 sm:w-48" />
        ) : (
          <Select value={categoryId || "all"} onValueChange={handleCategoryChange}>
            <SelectTrigger className="sm:w-48">
              <SelectValue placeholder="Category">
                {categoryId ? selectedCategoryLabel : "All categories"}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All categories</SelectItem>
              {categoryOptions.map((c) => (
                <SelectItem key={c.id} value={String(c.id)}>{c.label}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}

        <Select value={status || "none"} onValueChange={handleStatusChange}>
          <SelectTrigger className="sm:w-40">
            <SelectValue placeholder="Status">{selectedStatusLabel}</SelectValue>
          </SelectTrigger>
          <SelectContent>
            {STATUS_OPTIONS.map((s) => (
              <SelectItem key={s.value || "none"} value={s.value || "none"}>{s.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>

        <Select value={sort} onValueChange={handleSortChange}>
          <SelectTrigger className="sm:w-44">
            <SelectValue placeholder="Sort">{sortLabel(sort)}</SelectValue>
          </SelectTrigger>
          <SelectContent>
            {PRODUCT_SORT_OPTIONS.map((o) => (
              <SelectItem key={o.value} value={o.value}>{o.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {isLoading ? (
        <div className="space-y-3">
          {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-20 w-full" />)}
        </div>
      ) : isError ? (
        <p className="text-destructive">Could not load your products.</p>
      ) : data?.products.length === 0 ? (
        <p className="text-muted-foreground">No products match your filters.</p>
      ) : (
        <>
          <div className="space-y-3">
            {data?.products.map((product) => (
              <div key={product.id} className="flex items-center justify-between gap-4 rounded-lg border border-border bg-card p-4">
                <div className="flex items-center gap-3 overflow-hidden">
                  <div
                    className="h-14 w-14 shrink-0 rounded bg-muted bg-cover bg-center"
                    style={product.images[0] ? { backgroundImage: `url(${product.images[0].url})` } : undefined}
                  />
                  <div className="overflow-hidden">
                    <p className="truncate font-medium text-card-foreground">{product.name}</p>
                    <p className="text-sm text-muted-foreground">${product.price} · {product.stock_quantity} in stock</p>
                  </div>
                </div>
                <div className="flex shrink-0 items-center gap-2">
                  <Badge variant={product.is_active ? "default" : "secondary"}>
                    {product.is_active ? "Active" : "Inactive"}
                  </Badge>
                  <Button render={<Link href={`/vendor/products/${product.id}/edit`} />} variant="outline" size="sm">
                    Edit
                  </Button>
                  <AlertDialog>
                    <AlertDialogTrigger asChild><Button variant="outline" size="sm">Delete</Button></AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>Delete this product?</AlertDialogTitle>
                        <AlertDialogDescription>
                          This removes {product.name} and all of its images. This can&apos;t be undone.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction disabled={isDeleting} onClick={() => handleDelete(product.id)}>Delete</AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                </div>
              </div>
            ))}
          </div>
          {data && (
            <PaginationControls
              currentPage={data.meta.current_page}
              lastPage={data.meta.last_page}
              onPageChange={handlePageChange}
              disabled={isFetching}
            />
          )}
        </>
      )}
    </div>
  );
}