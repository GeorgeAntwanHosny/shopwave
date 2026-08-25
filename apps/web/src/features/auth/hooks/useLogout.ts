import { useMutation } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

export function useLogout() {
  const clearAuth = useAuthStore((s) => s.clearAuth);

  return useMutation({
    mutationFn: () => apiFetch("/api/v1/auth/logout", { method: "POST", auth: true }),
    onSuccess: () => clearAuth(),
  });
}