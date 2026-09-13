import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface FulfillmentPayload {
  fulfillment_status: "processing" | "shipped" | "delivered";
  tracking_number?: string;
  carrier?: string;
}

export function useUpdateOrderFulfillment(orderId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: FulfillmentPayload) =>
      apiFetch(`/api/v1/vendor/orders/${orderId}`, {
        method: "PUT",
        body: JSON.stringify(payload),
        auth: true,
      }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["vendor-order", orderId] });
      queryClient.invalidateQueries({ queryKey: ["vendor-orders"] });
    },
  });
}