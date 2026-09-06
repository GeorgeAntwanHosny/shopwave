import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export function useDeleteProduct() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/vendor/products/${id}`, { method: "DELETE", auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-products"] }),
  });
}