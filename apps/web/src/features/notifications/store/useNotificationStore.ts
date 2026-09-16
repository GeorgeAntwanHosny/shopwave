import { create } from "zustand";

export type NotificationType =
  | "NewOrderReceived"
  | "LowStockAlert"
  | "NewReviewPosted"
  | "OrderStatusChanged";

export interface NotificationItem {
  id: string;
  type: NotificationType;
  message: string;
  href?: string;
  createdAt: string;
  read: boolean;
}

interface NotificationState {
  notifications: NotificationItem[];
  addNotification: (item: Omit<NotificationItem, "id" | "read" | "createdAt">) => void;
  markAllRead: () => void;
  clear: () => void;
}

// Deliberately NOT persisted (no zustand `persist` middleware) — this is a
// live event feed for the current session, not a notification history.
// Cleared entirely on logout, same as the cart's guest token.
export const useNotificationStore = create<NotificationState>()((set) => ({
  notifications: [],
  addNotification: (item) =>
    set((state) => ({
      notifications: [
        { ...item, id: crypto.randomUUID(), read: false, createdAt: new Date().toISOString() },
        ...state.notifications,
      ].slice(0, 50), // cap so an idle-but-open tab can't grow this forever
    })),
  markAllRead: () =>
    set((state) => ({ notifications: state.notifications.map((n) => ({ ...n, read: true })) })),
  clear: () => set({ notifications: [] }),
}));