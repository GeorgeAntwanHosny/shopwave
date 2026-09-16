import { create } from "zustand";

export interface NotificationItem {
  id: string;
  type: string;
  message: string;
  href: string | null;
  createdAt: string;
  read: boolean;
}

interface NotificationState {
  notifications: NotificationItem[];
  hydrated: boolean;
  setInitial: (items: NotificationItem[]) => void;
  addNotification: (item: NotificationItem) => void;
  markAllRead: () => void;
  clear: () => void;
}

// Deliberately NOT persisted client-side (no zustand `persist`) — the
// database is now the source of truth (see NotificationController), this
// store just mirrors it for the current tab and appends live arrivals.
export const useNotificationStore = create<NotificationState>()((set) => ({
  notifications: [],
  hydrated: false,
  setInitial: (items) => set({ notifications: items, hydrated: true }),
  addNotification: (item) =>
    set((state) => {
      // De-duped by the real database ID (now included in the live
      // broadcast payload) — prevents a double entry if the history fetch
      // and a live event for the same notification land close together.
      if (state.notifications.some((n) => n.id === item.id)) return state;
      return { notifications: [item, ...state.notifications].slice(0, 50) };
    }),
  markAllRead: () =>
    set((state) => ({ notifications: state.notifications.map((n) => ({ ...n, read: true })) })),
  clear: () => set({ notifications: [], hydrated: false }),
}));