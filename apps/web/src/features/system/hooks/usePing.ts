import { useQuery } from "@tanstack/react-query";

interface PingResponse {
  status: string;
  service: string;
  timestamp: string;
}

async function fetchPing(): Promise<PingResponse> {
  const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/ping`);
  if (!res.ok) {
    throw new Error(`Ping failed with status ${res.status}`);
  }
  return res.json();
}

export function usePing() {
  return useQuery({
    queryKey: ["ping"],
    queryFn: fetchPing,
  });
}