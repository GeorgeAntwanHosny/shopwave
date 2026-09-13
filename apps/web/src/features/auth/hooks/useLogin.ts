import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { useCartStore } from "@/features/cart/store/useCartStore";

interface LoginPayload {
  email: string;
  password: string;
}

interface AuthResponse {
  user: { id: number; name: string; email: string };
  token: string;
}

export function useLogin() {
  const setAuth = useAuthStore((s) => s.setAuth);
  const clearGuestToken = useCartStore((s) => s.clearGuestToken);
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (payload: LoginPayload) =>
      apiFetch<AuthResponse>("/api/v1/auth/login", { method: "POST", body: JSON.stringify(payload) }),
    onSuccess: (data) => {
      queryClient.clear();
      setAuth(data.user, data.token);
      clearGuestToken();
    },
  });
}