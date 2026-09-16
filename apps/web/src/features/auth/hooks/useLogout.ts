import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { useNotificationStore } from "@/features/notifications/store/useNotificationStore";

export function useLogout() {
  const clearAuth = useAuthStore((s) => s.clearAuth);
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => apiFetch("/api/v1/auth/logout", { method: "POST", auth: true }),
    onSuccess: () => {
      queryClient.clear();
      useNotificationStore.getState().clear();
      clearAuth();
    },
  });
}