import { useMutation } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

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

  return useMutation({
    mutationFn: (payload: LoginPayload) =>
      apiFetch<AuthResponse>("/api/v1/auth/login", {
        method: "POST",
        body: JSON.stringify(payload),
      }),
    onSuccess: (data) => setAuth(data.user, data.token),
  });
}