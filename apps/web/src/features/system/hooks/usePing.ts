import { useQuery } from "@tanstack/react-query";
import { apiFetch } from "@/lib/api/client";

interface PingData {
  status: string;
  service: string;
  timestamp: string;
}

export function usePing() {
  return useQuery({
    queryKey: ["ping"],
    queryFn: () => apiFetch<PingData>("/api/v1/ping"),
  });
}