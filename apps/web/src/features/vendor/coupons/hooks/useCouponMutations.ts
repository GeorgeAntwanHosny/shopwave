import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import type { CouponFormValues } from "@/lib/validations/coupon";

export function useCreateCoupon() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: CouponFormValues) =>
      apiFetch("/api/v1/vendor/coupons", { method: "POST", body: JSON.stringify(payload), auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-coupons"] }),
  });
}

export function useUpdateCoupon(id: number) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: Partial<CouponFormValues>) =>
      apiFetch(`/api/v1/vendor/coupons/${id}`, { method: "PUT", body: JSON.stringify(payload), auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-coupons"] }),
  });
}

export function useDeleteCoupon() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => apiFetch(`/api/v1/vendor/coupons/${id}`, { method: "DELETE", auth: true }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-coupons"] }),
  });
}