import { useMutation } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import type { BecomeVendorFormValues } from "@/lib/validations/vendor";

interface BecomeVendorResponse {
  vendor: {
    id: number;
    shop_name: string;
    shop_slug: string;
    stripe_account_id: string;
    stripe_onboarding_complete: boolean;
  };
  onboarding_url: string;
}

export function useBecomeVendor() {
  return useMutation({
    mutationFn: (payload: BecomeVendorFormValues) =>
      apiFetch<BecomeVendorResponse>("/api/v1/vendor/onboard", {
        method: "POST",
        body: JSON.stringify(payload),
        auth: true,
      }),
  });
}