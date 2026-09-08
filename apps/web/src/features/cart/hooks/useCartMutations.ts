import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import type { CartResponse } from "@/features/cart/hooks/useCart";

export function useAddToCart() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: { product_id: number; quantity: number }) =>
      apiFetch<CartResponse>("/api/v1/cart/items", { method: "POST", body: JSON.stringify(payload), auth: true }),
    onSuccess: (data) => queryClient.setQueryData(["cart"], data),
  });
}

export function useUpdateCartItem() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ productId, quantity }: { productId: number; quantity: number }) =>
      apiFetch<CartResponse>(`/api/v1/cart/items/${productId}`, {
        method: "PUT",
        body: JSON.stringify({ quantity }),
        auth: true,
      }),
    onSuccess: (data) => queryClient.setQueryData(["cart"], data),
  });
}

export function useRemoveCartItem() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (productId: number) =>
      apiFetch<CartResponse>(`/api/v1/cart/items/${productId}`, { method: "DELETE", auth: true }),
    onSuccess: (data) => queryClient.setQueryData(["cart"], data),
  });
}

export function useApplyCoupon() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (code: string) =>
      apiFetch<CartResponse>("/api/v1/cart/coupon", { method: "POST", body: JSON.stringify({ code }), auth: true }),
    onSuccess: (data) => queryClient.setQueryData(["cart"], data),
  });
}

export function useRemoveCoupon() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: () => apiFetch<CartResponse>("/api/v1/cart/coupon", { method: "DELETE", auth: true }),
    onSuccess: (data) => queryClient.setQueryData(["cart"], data),
  });
}