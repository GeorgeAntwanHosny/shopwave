"use client";

import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { becomeVendorSchema, type BecomeVendorFormValues } from "@/lib/validations/vendor";
import { useBecomeVendor } from "@/features/vendor/hooks/useBecomeVendor";
import { ApiError } from "@/lib/api/client";

export function BecomeVendorCard() {
  const { mutate, isPending } = useBecomeVendor();

  const form = useForm<BecomeVendorFormValues>({
    resolver: zodResolver(becomeVendorSchema),
    defaultValues: { shop_name: "" },
  });

  function onSubmit(values: BecomeVendorFormValues) {
    mutate(values, {
      onSuccess: (data) => {
        toast.success("Stripe account created — redirecting to onboarding...");
        window.location.href = data.onboarding_url;
      },
      onError: (error) => {
        const err = error as ApiError;
        if (err.fieldErrors) {
          Object.entries(err.fieldErrors).forEach(([field, messages]) => {
            form.setError(field as keyof BecomeVendorFormValues, { message: messages[0] });
          });
        }
        toast.error(err.message || "Could not start vendor onboarding.");
      },
    });
  }

  return (
    <div className="rounded-lg border border-border bg-card p-6 shadow-sm sm:p-8">
      <h1 className="text-xl font-semibold text-card-foreground">Start selling on ShopWave</h1>
      <p className="mt-1 text-sm text-muted-foreground">
        Open your own shop and connect a Stripe account to receive payouts.
      </p>

      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="mt-6 space-y-4" noValidate>
          <FormField
            control={form.control}
            name="shop_name"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Shop name</FormLabel>
                <FormControl>
                  <Input placeholder="Jane's Boutique" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <Button type="submit" className="w-full" disabled={isPending}>
            {isPending ? "Creating your shop..." : "Become a vendor"}
          </Button>
        </form>
      </Form>
    </div>
  );
}