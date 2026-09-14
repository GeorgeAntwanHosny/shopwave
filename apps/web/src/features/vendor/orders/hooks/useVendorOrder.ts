import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface VendorOrderDetail {
  id: number;
  user: { name: string; email: string };
  subtotal: string;
  discount_amount: string;
  platform_fee_amount: string;
  vendor_payout_amount: string;
  total: string;
  status: string;
  fulfillment_status: "processing" | "shipped" | "delivered";
  tracking_number: string | null;
  carrier: string | null;
  transferred_at: string | null;
  created_at: string;
  items: {
    id: number;
    product_name: string;
    price: string;
    quantity: number;
    subtotal: string;
    review: {
      id: number;
      rating: number;
      comment: string | null;
      reply: { reply: string } | null;
    } | null;
  }[];
}

export function useVendorOrder(id: string) {
  return useQuery({
    queryKey: ["vendor-order", id],
    queryFn: () => apiFetch<VendorOrderDetail>(`/api/v1/vendor/orders/${id}`, { auth: true }),
    enabled: !!id,
  });
}