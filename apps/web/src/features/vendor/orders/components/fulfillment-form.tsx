"use client";

import { useState } from "react";
import { toast } from "sonner";
import { Loader2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useUpdateOrderFulfillment } from "@/features/vendor/orders/hooks/useUpdateOrderFulfillment";
import { ApiError } from "@/lib/api/client";

const STATUS_OPTIONS = [
  { value: "processing", label: "Processing" },
  { value: "shipped", label: "Shipped" },
  { value: "delivered", label: "Delivered" },
];

interface FulfillmentFormProps {
  orderId: string;
  currentStatus: string;
  currentTrackingNumber: string | null;
  currentCarrier: string | null;
}

export function FulfillmentForm({ orderId, currentStatus, currentTrackingNumber, currentCarrier }: FulfillmentFormProps) {
  const [status, setStatus] = useState(currentStatus);
  const [trackingNumber, setTrackingNumber] = useState(currentTrackingNumber ?? "");
  const [carrier, setCarrier] = useState(currentCarrier ?? "");
  const { mutate, isPending } = useUpdateOrderFulfillment(orderId);

  const statusLabel = STATUS_OPTIONS.find((s) => s.value === status)?.label;

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    mutate(
      {
        fulfillment_status: status as "processing" | "shipped" | "delivered",
        tracking_number: trackingNumber || undefined,
        carrier: carrier || undefined,
      },
      {
        onSuccess: () => toast.success("Order updated."),
        onError: (error) => toast.error((error as ApiError).message || "Could not update order."),
      }
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4 rounded-lg border border-border bg-card p-4">
      <p className="text-sm font-medium text-card-foreground">Fulfillment</p>

      <div className="space-y-2">
        <Label>Status</Label>
        <Select value={status} onValueChange={setStatus}>
          <SelectTrigger>
            <SelectValue>{statusLabel}</SelectValue>
          </SelectTrigger>
          <SelectContent>
            {STATUS_OPTIONS.map((s) => (
              <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="space-y-2">
          <Label>Carrier</Label>
          <Input value={carrier} onChange={(e) => setCarrier(e.target.value)} placeholder="UPS" />
        </div>
        <div className="space-y-2">
          <Label>Tracking number</Label>
          <Input value={trackingNumber} onChange={(e) => setTrackingNumber(e.target.value)} placeholder="1Z999AA10123456784" />
        </div>
      </div>

      <Button type="submit" disabled={isPending}>
        {isPending ? <><Loader2 className="mr-2 h-4 w-4 animate-spin" />Saving...</> : "Save"}
      </Button>
    </form>
  );
}