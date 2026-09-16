import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

export interface NotificationDTO {
  id: string;
  type: string;
  message: string;
  href: string | null;
  read: boolean;
  created_at: string;
}

export function useNotifications() {
  const token = useAuthStore((s) => s.token);

  return useQuery({
    queryKey: ["notifications"],
    queryFn: () => apiFetch<NotificationDTO[]>("/api/v1/notifications", { auth: true }),
    enabled: !!token,
  });
}