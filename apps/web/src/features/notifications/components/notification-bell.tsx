"use client";

import { useState } from "react";
import Link from "next/link";
import { Bell, CheckCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { useNotificationStore } from "@/features/notifications/store/useNotificationStore";
import { useMarkNotificationsRead } from "@/features/notifications/hooks/useMarkNotificationsRead";
import { getNotificationMeta } from "@/features/notifications/lib/notification-meta";
import { formatRelativeTime } from "@/features/notifications/lib/format-relative-time";

export function NotificationBell() {
  const [open, setOpen] = useState(false);
  const notifications = useNotificationStore((s) => s.notifications);
  const markAllReadLocal = useNotificationStore((s) => s.markAllRead);
  const { mutate: markAllReadServer } = useMarkNotificationsRead();
  const unreadCount = notifications.filter((n) => !n.read).length;

  function handleMarkAllRead() {
    markAllReadLocal();
    markAllReadServer();
  }

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button variant="outline" size="icon" className="relative shrink-0" aria-label="Notifications">
          <Bell className="h-4 w-4" />
          {unreadCount > 0 && (
            <span className="absolute -right-1 -top-1 flex h-4 min-w-4 animate-pulse items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
              {unreadCount > 9 ? "9+" : unreadCount}
            </span>
          )}
        </Button>
      </PopoverTrigger>
      <PopoverContent align="end" className="w-96 p-0">
        <div className="flex items-center justify-between border-b border-border p-3">
          <p className="text-sm font-semibold text-foreground">Notifications</p>
          {unreadCount > 0 && (
            <button
              onClick={handleMarkAllRead}
              className="flex items-center gap-1 text-xs font-medium text-primary hover:underline"
            >
              <CheckCheck className="h-3.5 w-3.5" />
              Mark all read
            </button>
          )}
        </div>
        <div className="max-h-96 overflow-y-auto">
          {notifications.length === 0 ? (
            <div className="p-8 text-center">
              <Bell className="mx-auto h-8 w-8 text-muted-foreground/40" />
              <p className="mt-2 text-sm text-muted-foreground">You&apos;re all caught up.</p>
            </div>
          ) : (
            notifications.map((n) => {
              const { title, icon: Icon, colorClass } = getNotificationMeta(n.type);
              return (
                <Link
                  key={n.id}
                  href={n.href ?? "#"}
                  onClick={() => setOpen(false)}
                  className={`flex gap-3 border-b border-border p-3 last:border-0 hover:bg-muted ${
                    !n.read ? "bg-primary/5" : ""
                  }`}
                >
                  <div className={`mt-0.5 h-fit shrink-0 rounded-full p-2 ${colorClass}`}>
                    <Icon className="h-4 w-4" />
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                      <p className="text-sm font-medium text-foreground">{title}</p>
                      {!n.read && <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />}
                    </div>
                    <p className="mt-0.5 truncate text-sm text-muted-foreground">{n.message}</p>
                    <p className="mt-1 text-xs text-muted-foreground/70">{formatRelativeTime(n.createdAt)}</p>
                  </div>
                </Link>
              );
            })
          )}
        </div>
      </PopoverContent>
    </Popover>
  );
}