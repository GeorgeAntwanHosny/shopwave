import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

interface LowStockProduct {
  id: number;
  name: string;
  slug: string;
  stock_quantity: number;
}

export function useVendorLowStock() {
  const token = useAuthStore((s) => s.token);

  return useQuery({
    queryKey: ["vendor-low-stock"],
    queryFn: () => apiFetch<LowStockProduct[]>("/api/v1/vendor/dashboard/low-stock", { auth: true }),
    enabled: !!token,
  });
}