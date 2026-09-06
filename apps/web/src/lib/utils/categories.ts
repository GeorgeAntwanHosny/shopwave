import type { CategoryNode } from "@/features/products/hooks/useCategories";

export interface FlatCategoryOption {
  id: number;
  label: string;
}

/** Flattens the nested category tree into a single list, indenting
 *  children so the hierarchy is still visible in a dropdown. */
export function flattenCategories(nodes: CategoryNode[], depth = 0): FlatCategoryOption[] {
  return nodes.flatMap((node) => [
    { id: node.id, label: `${"— ".repeat(depth)}${node.name}` },
    ...flattenCategories(node.children, depth + 1),
  ]);
}