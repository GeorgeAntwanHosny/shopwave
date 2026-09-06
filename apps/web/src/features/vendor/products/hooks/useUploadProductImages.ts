import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { ApiError } from "@/lib/api/client";

const API_URL = process.env.NEXT_PUBLIC_API_URL;

interface UploadEnvelope {
  success: boolean;
  message: string;
  data: unknown;
  errors: Record<string, string[]> | null;
}

export function useUploadProductImages(productId: string) {
  const queryClient = useQueryClient();
  const token = useAuthStore((s) => s.token);

  return useMutation({
    mutationFn: async (files: File[]) => {
      const formData = new FormData();
      files.forEach((file) => formData.append("images[]", file));

      const res = await fetch(`${API_URL}/api/v1/vendor/products/${productId}/images`, {
        method: "POST",
        headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
        body: formData,
      });

      const raw = await res.text();
      let body: UploadEnvelope | null = null;

      try {
        body = raw ? JSON.parse(raw) : null;
      } catch {
        throw new ApiError(
          res.status,
          null,
          "The upload server returned an invalid response — this usually means a server-side upload limit or temp-directory misconfiguration. Check the API server logs."
        );
      }

      if (!res.ok || !body?.success) {
        throw new ApiError(res.status, body?.errors ?? null, body?.message || "Upload failed.");
      }
      return body.data;
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["vendor-product", productId] }),
  });
}