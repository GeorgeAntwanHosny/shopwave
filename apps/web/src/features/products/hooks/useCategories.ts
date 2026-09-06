import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface CategoryNode {
  id: number;
  name: string;
  slug: string;
  children: CategoryNode[];
}

export function useCategories() {
  return useQuery({
    queryKey: ["categories"],
    queryFn: () => apiFetch<CategoryNode[]>("/api/v1/categories"),
    staleTime: 5 * 60 * 1000,
  });
}