import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

interface VendorSummary {
  id: number;
  shop_name: string;
  shop_slug: string;
  stripe_onboarding_complete: boolean;
}

interface MeResponse {
  user: {
    id: number;
    name: string;
    email: string;
    vendor: VendorSummary | null;
    is_admin: boolean;
  };
}

export function useMe() {
  const token = useAuthStore((s) => s.token);

  return useQuery({
    queryKey: ["me"],
    queryFn: () => apiFetch<MeResponse>("/api/v1/auth/me", { auth: true }),
    enabled: !!token,
  });
}