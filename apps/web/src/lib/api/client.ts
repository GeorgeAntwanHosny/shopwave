import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { useCartStore } from "@/features/cart/store/useCartStore";

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
  const cartToken = useCartStore.getState().guestToken;

  const res = await fetch(`${API_URL}${path}`, {
    ...rest,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(cartToken ? { "X-Cart-Token": cartToken } : {}),
      ...headers,
    },
  });

  const raw = await res.text();
  let body: ApiEnvelope<T> | null = null;

  try {
    body = raw ? JSON.parse(raw) : null;
  } catch {
    throw new ApiError(
      res.status,
      null,
      res.ok
        ? "The server returned an unexpected response. Please try again."
        : `Request failed with status ${res.status}.`
    );
  }

  if (!res.ok || !body?.success) {
    throw new ApiError(res.status, body?.errors ?? null, body?.message || "Something went wrong.");
  }

  // Cart endpoints echo back a guest cart token — capture it here once,
  // in one place, rather than in every individual cart hook.
  const data = body.data as Record<string, unknown> | null;
  if (data && typeof data === "object" && typeof data.cart_token === "string") {
    useCartStore.getState().setGuestToken(data.cart_token);
  }

  return body.data as T;
}