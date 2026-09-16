"use client";

import { useRealtimeNotifications } from "@/features/notifications/hooks/useRealtimeNotifications";

/** Mounted once, globally — sets up the websocket listeners for the whole
 *  session. Renders nothing; a pure side-effect component. */
export function NotificationListener() {
  useRealtimeNotifications();
  return null;
}