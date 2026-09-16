"use client";

import { useEffect } from "react";
import { getEcho } from "@/lib/echo";
import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { useMe } from "@/features/auth/hooks/useMe";
import { useNotifications, type NotificationDTO } from "@/features/notifications/hooks/useNotifications";
import { useNotificationStore, type NotificationItem } from "@/features/notifications/store/useNotificationStore";
import { showNotificationToast } from "@/features/notifications/components/notification-toast";

export function useRealtimeNotifications() {
  const token = useAuthStore((s) => s.token);
  const { data: me } = useMe();
  const { data: history } = useNotifications();
  const setInitial = useNotificationStore((s) => s.setInitial);
  const addNotification = useNotificationStore((s) => s.addNotification);

  // Seed the bell with persisted history the moment it loads — anything
  // that fired while this tab was closed is still here, not just live ones.
  useEffect(() => {
    if (!history) return;
    setInitial(
      history.map((n) => ({
        id: n.id,
        type: n.type,
        message: n.message,
        href: n.href,
        createdAt: n.created_at,
        read: n.read,
      }))
    );
  }, [history, setInitial]);

  useEffect(() => {
    if (!token || !me) return;

    const echo = getEcho(token);
    if (!echo) return;

    function handle(e: NotificationDTO) {
      console.log("new notification:", e);
      const item: NotificationItem = {
        id: e.id,
        type: e.type,
        message: e.message,
        href: e.href,
        createdAt: new Date().toISOString(),
        read: false,
      };
      addNotification(item);
      showNotificationToast(item);
    }
   // 1. Public Test Channel
  const testChannel = echo.channel("test");
  testChannel.listen(".TestEvent", handle);

  // 2. Private User Channel (using .notification())
  const userChannelName = `user.${me.user.id}`;
  const userChannel = echo.private(userChannelName);

  // .notification() automatically unwraps Laravel Notification payloads!
  userChannel.notification(handle);

  // 3. Private Vendor Channel (if vendor exists)
  const vendorId = me.user.vendor?.id;
  let vendorChannelName: string | null = null;

  if (vendorId) {
    vendorChannelName = `vendor.${vendorId}`;
    const vendorChannel = echo.private(vendorChannelName);
    vendorChannel.notification(handle);
  }

    return () => {
      echo.leave(userChannelName);
      if (vendorChannelName) echo.leave(vendorChannelName);
    };
  }, [token, me, addNotification]);
}