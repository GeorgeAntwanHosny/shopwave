"use client";

import { useEffect } from "react";
import { toast } from "sonner";
import { getEcho } from "@/lib/echo";
import { useAuthStore } from "@/features/auth/store/useAuthStore";
import { useMe } from "@/features/auth/hooks/useMe";
import { useNotificationStore } from "@/features/notifications/store/useNotificationStore";

export function useRealtimeNotifications() {
  const token = useAuthStore((s) => s.token);
  const { data: me } = useMe();
  const addNotification = useNotificationStore((s) => s.addNotification);

  useEffect(() => {
    if (!token || !me) return;

    const echo = getEcho(token);
    if (!echo) return;

    // Store channel references
  const testChannel = echo.channel("test");
  testChannel.listen(".TestEvent", (e: any) => {
    console.log("✅ PUBLIC CHANNEL RECEIVED:", e);
  });

    const userChannelName = `user.${me.user.id}`;
    const userChannel = echo.private(userChannelName);
   console.log("Listening to user channel:", userChannelName);

    userChannel.listen(".OrderStatusChanged", (e: { order_id: number; fulfillment_status: string }) => {
      const message = `Your order #${e.order_id} is now ${e.fulfillment_status}.`;
      console.log("OrderStatusChanged event received:", e);
      addNotification({ type: "OrderStatusChanged", message, href: `/orders/${e.order_id}` });
      toast.info(message);
    });

    const vendorId = me.user.vendor?.id;
    let vendorChannelName: string | null = null;

    if (vendorId) {
      vendorChannelName = `vendor.${vendorId}`;
      const vendorChannel = echo.private(vendorChannelName);

      vendorChannel.listen(
        ".NewOrderReceived",
        (e: { order_id: number; customer_name: string; total: string }) => {
          const message = `New order #${e.order_id} from ${e.customer_name} — $${e.total}`;
          console.log("NewOrderReceived event received:", e);
          addNotification({ type: "NewOrderReceived", message, href: `/vendor/orders/${e.order_id}` });
          toast.success(message);
        }
      );

      vendorChannel.listen(
        ".LowStockAlert",
        (e: { product_id: number; product_name: string; stock_quantity: number }) => {
          const message = `${e.product_name} is low on stock (${e.stock_quantity} left).`;
          console.log("LowStockAlert event received:", e);
          addNotification({ type: "LowStockAlert", message, href: `/vendor/products/${e.product_id}/edit` });
          toast.warning(message);
        }
      );

      vendorChannel.listen(
        ".NewReviewPosted",
        (e: { review_id: number; product_name: string; rating: number }) => {
          const message = `New ${e.rating}★ review on ${e.product_name}.`;
          console.log("NewReviewPosted event received:", e);
          addNotification({ type: "NewReviewPosted", message, href: "/vendor/products" });
          toast.info(message);
        }
      );
    }

    return () => {
      echo.leave(userChannelName);
      if (vendorChannelName) echo.leave(vendorChannelName);
    };
  }, [token, me, addNotification]);
}