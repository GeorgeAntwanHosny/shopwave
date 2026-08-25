import { create } from "zustand";
import { persist } from "zustand/middleware";

interface AuthUser {
  id: number;
  name: string;
  email: string;
}

interface AuthState {
  user: AuthUser | null;
  token: string | null;
  setAuth: (user: AuthUser, token: string) => void;
  clearAuth: () => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      token: null,
      setAuth: (user, token) => {
        set({ user, token });
        document.cookie = `shopwave_token=${token}; path=/; max-age=${60 * 60 * 24 * 7}`;
      },
      clearAuth: () => {
        set({ user: null, token: null });
        document.cookie = "shopwave_token=; path=/; max-age=0";
      },
    }),
    { name: "shopwave-auth" }
  )
);