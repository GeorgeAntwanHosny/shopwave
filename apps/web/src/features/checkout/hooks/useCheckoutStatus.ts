import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface CheckoutOrder {
  id: number;
  vendor: { shop_name: string };
  total: string;
  items: { product_name: string; quantity: number; price: string }[];
}

interface CheckoutStatusResponse {
  status: "pending" | "completed" | "failed";
  orders: CheckoutOrder[];
}

export function useCheckoutStatus(paymentIntentId: string | null) {
  return useQuery({
    queryKey: ["checkout-status", paymentIntentId],
    queryFn: () => apiFetch<CheckoutStatusResponse>(`/api/v1/checkout/${paymentIntentId}/status`, { auth: true }),
    enabled: !!paymentIntentId,
    refetchInterval: (query) => (query.state.data?.status === "pending" ? 1500 : false),
  });
}