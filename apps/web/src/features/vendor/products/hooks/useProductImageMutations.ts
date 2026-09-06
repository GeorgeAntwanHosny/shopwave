import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export function useDeleteProductImage(productId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (imageId: number) =>
      apiFetch(`/api/v1/vendor/products/${productId}/images/${imageId}`, { method: "DELETE", auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-product", productId] }),
  });
}

export function useReorderProductImages(productId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (imageIds: number[]) =>
      apiFetch(`/api/v1/vendor/products/${productId}/images/reorder`, {
        method: "PUT",
        body: JSON.stringify({ image_ids: imageIds }),
        auth: true,
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-product", productId] }),
  });
}