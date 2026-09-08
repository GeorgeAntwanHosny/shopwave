import { useMutation, useQueryClient } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { useCartStore } from "@/features/cart/store/useCartStore";

interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

interface AuthResponse {
  user: { id: number; name: string; email: string };
  token: string;
}

export function useRegister() {
  const setAuth = useAuthStore((s) => s.setAuth);
  const clearGuestToken = useCartStore((s) => s.clearGuestToken);
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (payload: RegisterPayload) =>
      apiFetch<AuthResponse>("/api/v1/auth/register", { method: "POST", body: JSON.stringify(payload) }),
    onSuccess: (data) => {
      setAuth(data.user, data.token);
      clearGuestToken();
      queryClient.invalidateQueries({ queryKey: ["cart"] });
    },
  });
}