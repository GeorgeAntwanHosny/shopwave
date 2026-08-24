"use client";

import { usePing } from "@/features/system/hooks/usePing";

export default function PingPage() {
  const { data, isLoading, isError, error } = usePing();

  if (isLoading) return <p>Pinging API...</p>;
  if (isError) return <p>Ping failed: {(error as Error).message}</p>;

  return (
    <div>
      <h1>API Reachable ✅</h1>
      <pre>{JSON.stringify(data, null, 2)}</pre>
    </div>
  );
}