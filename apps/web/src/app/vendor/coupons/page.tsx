"use client";

import { useState } from "react";
import { toast } from "sonner";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Loader2, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { Switch } from "@/components/ui/switch";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { couponSchema, type CouponFormValues } from "@/lib/validations/coupon";
import { useVendorCoupons, type VendorCoupon } from "@/features/vendor/coupons/hooks/useVendorCoupons";
import { useCreateCoupon, useUpdateCoupon, useDeleteCoupon } from "@/features/vendor/coupons/hooks/useCouponMutations";
import { ApiError } from "@/lib/api/client";

const TYPE_LABELS = { percentage: "Percentage", fixed: "Fixed amount" } as const;

function CouponRow({ coupon }: { coupon: VendorCoupon }) {
  const updateCoupon = useUpdateCoupon(coupon.id);
  const deleteCoupon = useDeleteCoupon();

  function toggleActive(checked: boolean) {
    updateCoupon.mutate(
      { is_active: checked },
      {
        onSuccess: () => toast.success(checked ? "Coupon activated." : "Coupon deactivated."),
        onError: () => toast.error("Could not update coupon."),
      }
    );
  }

  function handleDelete() {
    deleteCoupon.mutate(coupon.id, {
      onSuccess: () => toast.success("Coupon deleted."),
      onError: () => toast.error("Could not delete coupon."),
    });
  }

  return (
    <div className="flex items-center justify-between gap-4 rounded-lg border border-border bg-card p-4">
      <div>
        <p className="font-mono font-medium text-card-foreground">{coupon.code}</p>
        <p className="text-sm text-muted-foreground">
          {TYPE_LABELS[coupon.type]} · {coupon.type === "percentage" ? `${coupon.value}%` : `$${coupon.value}`} off
          {coupon.min_order_amount ? ` · min $${coupon.min_order_amount}` : ""}
          {coupon.max_uses ? ` · ${coupon.used_count}/${coupon.max_uses} used` : ` · ${coupon.used_count} used`}
        </p>
      </div>
      <div className="flex items-center gap-3">
        <Badge variant={coupon.is_active ? "default" : "secondary"}>{coupon.is_active ? "Active" : "Inactive"}</Badge>
        <Switch checked={coupon.is_active} onCheckedChange={toggleActive} disabled={updateCoupon.isPending} />
        <AlertDialog>
          <AlertDialogTrigger render={<Button variant="outline" size="icon" />}>
            <Trash2 className="h-4 w-4" />
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete this coupon?</AlertDialogTitle>
              <AlertDialogDescription>
                Customers will no longer be able to apply {coupon.code}. This can&apos;t be undone.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction disabled={deleteCoupon.isPending} onClick={handleDelete}>Delete</AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>
    </div>
  );
}

export default function VendorCouponsPage() {
  const { data: coupons, isLoading, isError } = useVendorCoupons();
  const { mutate: createCoupon, isPending: isCreating } = useCreateCoupon();
  const [showForm, setShowForm] = useState(false);

  const form = useForm<CouponFormValues>({
    resolver: zodResolver(couponSchema),
    defaultValues: { code: "", type: "percentage", value: 10, is_active: true },
  });

  function onSubmit(values: CouponFormValues) {
    createCoupon(values, {
      onSuccess: () => {
        toast.success("Coupon created.");
        form.reset({ code: "", type: "percentage", value: 10, is_active: true });
        setShowForm(false);
      },
      onError: (error) => {
        const err = error as ApiError;
        if (err.fieldErrors) {
          Object.entries(err.fieldErrors).forEach(([field, messages]) => {
            form.setError(field as keyof CouponFormValues, { message: messages[0] });
          });
        }
        toast.error(err.message || "Could not create coupon.");
      },
    });
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6 p-4 sm:p-6 lg:p-8">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold text-foreground">Coupons</h1>
        <Button onClick={() => setShowForm((s) => !s)}>{showForm ? "Cancel" : "New coupon"}</Button>
      </div>

      {showForm && (
        <div className="rounded-lg border border-border bg-card p-6">
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4" noValidate>
              <FormField control={form.control} name="code" render={({ field }) => (
                <FormItem>
                  <FormLabel>Code</FormLabel>
                  <FormControl><Input placeholder="SAVE10" {...field} /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <div className="grid grid-cols-2 gap-4">
                <FormField control={form.control} name="type" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Type</FormLabel>
                    <Select value={field.value} onValueChange={field.onChange}>
                      <FormControl>
                        <SelectTrigger>
                          <SelectValue>{TYPE_LABELS[field.value]}</SelectValue>
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        <SelectItem value="percentage">Percentage</SelectItem>
                        <SelectItem value="fixed">Fixed amount</SelectItem>
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )} />
                <FormField control={form.control} name="value" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Value</FormLabel>
                    <FormControl><Input type="number" step="0.01" {...field} /></FormControl>
                    <FormMessage />
                  </FormItem>
                )} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <FormField control={form.control} name="min_order_amount" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Min order ($, optional)</FormLabel>
                    <FormControl><Input type="number" step="0.01" {...field} /></FormControl>
                    <FormMessage />
                  </FormItem>
                )} />
                <FormField control={form.control} name="max_uses" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Max uses (optional)</FormLabel>
                    <FormControl><Input type="number" {...field} /></FormControl>
                    <FormMessage />
                  </FormItem>
                )} />
              </div>
              <FormField control={form.control} name="expires_at" render={({ field }) => (
                <FormItem>
                  <FormLabel>Expires (optional)</FormLabel>
                  <FormControl><Input type="date" {...field} /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <Button type="submit" className="w-full" disabled={isCreating}>
                {isCreating ? <><Loader2 className="mr-2 h-4 w-4 animate-spin" />Creating...</> : "Create coupon"}
              </Button>
            </form>
          </Form>
        </div>
      )}

      {isLoading ? (
        <div className="space-y-3">
          {Array.from({ length: 2 }).map((_, i) => <Skeleton key={i} className="h-20 w-full" />)}
        </div>
      ) : isError ? (
        <p className="text-destructive">Could not load your coupons.</p>
      ) : coupons?.length === 0 ? (
        <p className="text-muted-foreground">No coupons yet.</p>
      ) : (
        <div className="space-y-3">
          {coupons?.map((coupon) => <CouponRow key={coupon.id} coupon={coupon} />)}
        </div>
      )}
    </div>
  );
}