import { useAuthStore } from "@/features/auth/store/useAuthStore";

const API_URL = process.env.NEXT_PUBLIC_API_URL;

interface ApiEnvelope<T> {
  success: boolean;
  message: string;
  data: T | null;
  errors: Record<string, string[]> | null;
}

interface ApiOptions extends RequestInit {
  auth?: boolean;
}

export class ApiError extends Error {
  constructor(
    public status: number,
    public fieldErrors: Record<string, string[]> | null,
    message: string
  ) {
    super(message);
  }
}

export async function apiFetch<T>(path: string, options: ApiOptions = {}): Promise<T> {
  const { auth = false, headers, ...rest } = options;
  const token = auth ? useAuthStore.getState().token : null;

  const res = await fetch(`${API_URL}${path}`, {
    ...rest,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...headers,
    },
  });

  const body: ApiEnvelope<T> = await res.json();

  if (!res.ok || !body.success) {
    throw new ApiError(res.status, body.errors, body.message || "Something went wrong.");
  }

  return body.data as T;
}