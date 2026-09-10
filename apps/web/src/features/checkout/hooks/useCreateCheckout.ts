import { useMutation } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface CheckoutResponse {
  client_secret: string;
  payment_intent_id: string;
}

export function useCreateCheckout() {
  return useMutation({
    mutationFn: () => apiFetch<CheckoutResponse>("/api/v1/checkout", { method: "POST", auth: true }),
  });
}