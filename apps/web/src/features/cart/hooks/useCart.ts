import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

export interface CartItem {
  product_id: number;
  name: string;
  slug: string;
  price: string;
  quantity: number;
  effective_quantity: number;
  stock_limited: boolean;
  available_stock: number;
  image: string | null;
  subtotal: string;
}

export interface CartVendorGroup {
  vendor_id: number;
  shop_name: string;
  items: CartItem[];
  subtotal: string;
  coupon: { code: string; type: "percentage" | "fixed"; value: string; discount_amount: string } | null;
  total_after_discount: string;
}

export interface CartResponse {
  vendors: CartVendorGroup[];
  unavailable_items: { product_id: number; reason: string }[];
  grand_total: string;
  item_count: number;
  cart_token: string | null;
}

export function useCart() {
  return useQuery({
    queryKey: ["cart"],
    queryFn: () => apiFetch<CartResponse>("/api/v1/cart", { auth: true }),
  });
}