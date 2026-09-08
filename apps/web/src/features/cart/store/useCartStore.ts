import { create } from "zustand";
import { persist } from "zustand/middleware";

interface CartUIState {
  guestToken: string | null;
  isCartOpen: boolean;
  setGuestToken: (token: string) => void;
  clearGuestToken: () => void;
  openCart: () => void;
  closeCart: () => void;
  toggleCart: () => void;
}

export const useCartStore = create<CartUIState>()(
  persist(
    (set) => ({
      guestToken: null,
      isCartOpen: false,
      setGuestToken: (token) => set({ guestToken: token }),
      clearGuestToken: () => set({ guestToken: null }),
      openCart: () => set({ isCartOpen: true }),
      closeCart: () => set({ isCartOpen: false }),
      toggleCart: () => set((s) => ({ isCartOpen: !s.isCartOpen })),
    }),
    { name: "shopwave-cart-ui" }
  )
);