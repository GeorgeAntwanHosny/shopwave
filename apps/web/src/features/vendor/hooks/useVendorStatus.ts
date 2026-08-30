import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

interface VendorStatusResponse {
  vendor: {
    id: number;
    shop_name: string;
    shop_slug: string;
    stripe_onboarding_complete: boolean;
  };
  onboarding_url: string | null;
}

export function useVendorStatus(enabled: boolean) {
  const token = useAuthStore((s) => s.token);

  return useQuery({
    queryKey: ["vendor-status"],
    queryFn: () => apiFetch<VendorStatusResponse>("/api/v1/vendor/status", { auth: true }),
    enabled: enabled && !!token,
    retry: false,
  });
}