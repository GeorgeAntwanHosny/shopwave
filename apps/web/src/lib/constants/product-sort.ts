export const PRODUCT_SORT_OPTIONS = [
  { value: "newest", label: "Newest" },
  { value: "price_asc", label: "Price: low to high" },
  { value: "price_desc", label: "Price: high to low" },
  { value: "rating", label: "Top rated" },
] as const;

export function sortLabel(value: string): string {
  return PRODUCT_SORT_OPTIONS.find((o) => o.value === value)?.label ?? "Sort";
}