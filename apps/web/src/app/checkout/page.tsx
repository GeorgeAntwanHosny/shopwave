"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { Elements } from "@stripe/react-stripe-js";
import { toast } from "sonner";
import { getStripe } from "@/lib/stripe";
import { useCreateCheckout } from "@/features/checkout/hooks/useCreateCheckout";
import { useCart } from "@/features/cart/hooks/useCart";
import { CheckoutForm } from "@/features/checkout/components/checkout-form";
import { Skeleton } from "@/components/ui/skeleton";
import { ApiError } from "@/lib/api/client";

export default function CheckoutPage() {
  const router = useRouter();
  const { data: cart, isLoading: cartLoading } = useCart();
  const { mutate: createCheckout, data, isError, error } = useCreateCheckout();
  const [started, setStarted] = useState(false);

  useEffect(() => {
    if (!cartLoading && cart && cart.vendors.length > 0 && !started) {
      setStarted(true);
      createCheckout(undefined, {
        onError: (err) => toast.error((err as ApiError).message || "Could not start checkout."),
      });
    }
  }, [cartLoading, cart, started, createCheckout]);

  if (!cartLoading && cart && cart.vendors.length === 0) {
    return (
      <div className="mx-auto max-w-lg space-y-4 p-4 text-center sm:p-6 lg:p-8">
        <p className="text-muted-foreground">Your cart is empty.</p>
        <button onClick={() => router.push("/products")} className="text-primary underline">
          Browse products
        </button>
      </div>
    );
  }

  if (isError) {
    return (
      <div className="mx-auto max-w-lg p-4 text-center sm:p-6 lg:p-8">
        <p className="text-destructive">{(error as ApiError)?.message || "Could not start checkout."}</p>
      </div>
    );
  }

  if (cartLoading || !data) {
    return (
      <div className="mx-auto max-w-lg space-y-4 p-4 sm:p-6 lg:p-8">
        <Skeleton className="h-8 w-40" />
        <Skeleton className="h-64 w-full" />
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-lg space-y-6 p-4 sm:p-6 lg:p-8">
      <div>
        <h1 className="text-2xl font-semibold text-foreground">Checkout</h1>
        <p className="text-muted-foreground">Total: ${cart?.grand_total}</p>
      </div>
      <Elements stripe={getStripe()} options={{ clientSecret: data.client_secret }}>
        <CheckoutForm />
      </Elements>
    </div>
  );
}