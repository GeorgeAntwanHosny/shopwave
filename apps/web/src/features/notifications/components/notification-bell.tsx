"use client";

import Link from "next/link";
import { Bell } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { useNotificationStore } from "@/features/notifications/store/useNotificationStore";

export function NotificationBell() {
  const notifications = useNotificationStore((s) => s.notifications);
  const markAllRead = useNotificationStore((s) => s.markAllRead);
  const unreadCount = notifications.filter((n) => !n.read).length;

  return (
    <Popover onOpenChange={(open) => open && markAllRead()}>
        <PopoverTrigger
            render={(props) => (
                <Button
                {...props}
                variant="outline"
                size="icon"
                className="relative shrink-0"
                aria-label="Notifications"
                >
                <Bell className="h-4 w-4" />
                {unreadCount > 0 && (
                    <span className="absolute -right-1 -top-1 flex h-4 min-w-4 animate-pulse items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
                    {unreadCount}
                    </span>
                )}
                </Button>
            )}
        />
      <PopoverContent align="end" className="w-80 p-0">
        <div className="border-b border-border p-3">
          <p className="text-sm font-medium text-foreground">Notifications</p>
        </div>
        <div className="max-h-80 overflow-y-auto">
          {notifications.length === 0 ? (
            <p className="p-4 text-center text-sm text-muted-foreground">No notifications yet.</p>
          ) : (
            notifications.map((n) => (
              <Link
                key={n.id}
                href={n.href ?? "#"}
                className="block border-b border-border p-3 text-sm last:border-0 hover:bg-muted"
              >
                <p className="text-foreground">{n.message}</p>
                <p className="mt-1 text-xs text-muted-foreground">
                  {new Date(n.createdAt).toLocaleTimeString()}
                </p>
              </Link>
            ))
          )}
        </div>
      </PopoverContent>
    </Popover>
  );
}