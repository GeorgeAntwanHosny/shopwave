import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import type { ProductFormValues } from "@/lib/validations/product";

export function useCreateProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: ProductFormValues) =>
      apiFetch<{ id: number }>("/api/v1/vendor/products", {
        method: "POST",
        body: JSON.stringify(payload),
        auth: true,
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-products"] }),
  });
}