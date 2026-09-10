"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useQueryClient } from "@tanstack/react-query";
import { PaymentElement, useElements, useStripe } from "@stripe/react-stripe-js";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";

export function CheckoutForm() {
  const stripe = useStripe();
  const elements = useElements();
  const router = useRouter();
  const queryClient = useQueryClient();
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!stripe || !elements) return;

    setIsSubmitting(true);

    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      redirect: "if_required",
      confirmParams: {
        return_url: `${window.location.origin}/checkout/success`,
      },
    });

    if (error) {
      toast.error(error.message || "Payment failed. Please try again.");
      setIsSubmitting(false);
      return;
    }

    if (paymentIntent?.status === "succeeded") {
      // Clear the cached cart immediately client-side
      queryClient.invalidateQueries({ queryKey: ["cart"] });
      router.push(`/checkout/success?payment_intent=${paymentIntent.id}`);
      return;
    }

    setIsSubmitting(false);
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <PaymentElement />
      <Button type="submit" className="w-full" disabled={!stripe || isSubmitting}>
        {isSubmitting ? "Processing..." : "Pay now"}
      </Button>
    </form>
  );
}