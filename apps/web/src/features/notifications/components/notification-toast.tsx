"use client";

import Link from "next/link";
import { toast } from "sonner";
import { X } from "lucide-react";
import { getNotificationMeta } from "@/features/notifications/lib/notification-meta";
import type { NotificationItem } from "@/features/notifications/store/useNotificationStore";

export function showNotificationToast(item: NotificationItem) {
  toast.custom((toastId) => <NotificationToastContent item={item} toastId={toastId} />, { duration: 6000 });
}

function NotificationToastContent({ item, toastId }: { item: NotificationItem; toastId: string | number }) {
  const { title, icon: Icon, colorClass } = getNotificationMeta(item.type);

  // Stops the click from reaching the wrapping <Link>, so the close button
  // dismisses without also navigating.
  function handleDismiss(e: React.MouseEvent) {
    e.preventDefault();
    e.stopPropagation();
    toast.dismiss(toastId);
  }

  const card = (
    <div className="flex w-[360px] items-start gap-3 rounded-lg border border-border bg-card p-4 shadow-lg transition-colors hover:bg-muted/50">
      <div className={`mt-0.5 shrink-0 rounded-full p-2 ${colorClass}`}>
        <Icon className="h-4 w-4" />
      </div>
      <div className="min-w-0 flex-1">
        <p className="text-sm font-semibold text-card-foreground">{title}</p>
        <p className="mt-0.5 text-sm text-muted-foreground">{item.message}</p>
      </div>
      <button
        onClick={handleDismiss}
        className="shrink-0 rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
        aria-label="Dismiss"
      >
        <X className="h-3.5 w-3.5" />
      </button>
    </div>
  );

  if (!item.href) {
    return card;
  }

  return (
    <Link href={item.href} onClick={() => toast.dismiss(toastId)} className="block cursor-pointer">
      {card}
    </Link>
  );
}