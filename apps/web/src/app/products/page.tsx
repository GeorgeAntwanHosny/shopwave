"use client";

import { useState } from "react";
import { useProducts } from "@/features/products/hooks/useProducts";
import { useCategories } from "@/features/products/hooks/useCategories";
import { ProductCard } from "@/features/products/components/product-card";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { flattenCategories } from "@/lib/utils/categories";
import { PRODUCT_SORT_OPTIONS, sortLabel } from "@/lib/constants/product-sort";
import { useDebouncedValue } from "@/lib/hooks/useDebouncedValue";
import { PaginationControls } from "@/components/pagination-controls";

export default function ProductsPage() {
  const [qInput, setQInput] = useState("");
  const [categoryId, setCategoryId] = useState("");
  const [sort, setSort] = useState("newest");
  const [page, setPage] = useState(1);
  const q = useDebouncedValue(qInput, 400);

  const { data, isLoading, isFetching, isError } = useProducts({ q, category_id: categoryId, sort, page });
  const { data: categoryTree, isLoading: categoriesLoading } = useCategories();
  const categoryOptions = categoryTree ? flattenCategories(categoryTree) : [];
  const selectedCategoryLabel = categoryOptions.find((c) => String(c.id) === categoryId)?.label;

  function handleSearchChange(value: string) {
    setQInput(value);
    setPage(1);
  }
  function handleCategoryChange(value: string) {
    setCategoryId(value === "all" ? "" : value);
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

  return (
    <div className="mx-auto max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
      <h1 className="text-2xl font-semibold text-foreground">Browse products</h1>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <Input
          placeholder="Search products..."
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
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          {Array.from({ length: 8 }).map((_, i) => <Skeleton key={i} className="aspect-square w-full" />)}
        </div>
      ) : isError ? (
        <p className="text-destructive">Could not load products. Please try again.</p>
      ) : data?.products.length === 0 ? (
        <p className="text-muted-foreground">No products match your filters.</p>
      ) : (
        <>
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            {data?.products.map((p) => <ProductCard key={p.id} product={p} />)}
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