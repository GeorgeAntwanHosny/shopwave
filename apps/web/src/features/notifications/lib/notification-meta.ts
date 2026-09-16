import { AlertTriangle, Bell, MessageSquare, Package, Star, Truck, type LucideIcon } from "lucide-react";

interface NotificationMeta {
  title: string;
  icon: LucideIcon;
  colorClass: string;
}

const META: Record<string, NotificationMeta> = {
  NewOrderReceived: {
    title: "New order",
    icon: Package,
    colorClass: "bg-green-500/10 text-green-600 dark:text-green-400",
  },
  LowStockAlert: {
    title: "Low stock",
    icon: AlertTriangle,
    colorClass: "bg-amber-500/10 text-amber-600 dark:text-amber-400",
  },
  NewReviewPosted: {
    title: "New review",
    icon: Star,
    colorClass: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  },
  OrderStatusChanged: {
    title: "Order update",
    icon: Truck,
    colorClass: "bg-purple-500/10 text-purple-600 dark:text-purple-400",
  },
  ReviewReplyPosted: {
    title: "Vendor replied",
    icon: MessageSquare,
    colorClass: "bg-pink-500/10 text-pink-600 dark:text-pink-400",
  },
};

export function getNotificationMeta(type: string): NotificationMeta {
  return META[type] ?? { title: "Notification", icon: Bell, colorClass: "bg-muted text-muted-foreground" };
}   