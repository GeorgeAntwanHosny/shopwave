import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import type { ProductFormValues } from "@/lib/validations/product";

export function useUpdateProduct(id: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: Partial<ProductFormValues>) =>
      apiFetch(`/api/v1/vendor/products/${id}`, {
        method: "PUT",
        body: JSON.stringify(payload),
        auth: true,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["vendor-products"] });
      queryClient.invalidateQueries({ queryKey: ["vendor-product", id] });
    },
  });
}