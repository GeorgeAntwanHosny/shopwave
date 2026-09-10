import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface OrderDetail {
  id: number;
  vendor: { shop_name: string };
  subtotal: string;
  discount_amount: string;
  total: string;
  status: string;
  created_at: string;
  items: { id: number; product_name: string; price: string; quantity: number; subtotal: string }[];
}

export function useOrder(id: string) {
  return useQuery({
    queryKey: ["order", id],
    queryFn: () => apiFetch<OrderDetail>(`/api/v1/orders/${id}`, { auth: true }),
    enabled: !!id,
  });
}