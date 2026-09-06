"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { useVendorStatus } from "@/features/vendor/hooks/useVendorStatus";

export function OnboardingStatus() {
  const { data, isLoading, isError } = useVendorStatus(true);

  if (isLoading) {
    return (
      <div className="w-full max-w-md space-y-4">
        <Skeleton className="h-6 w-40" />
        <Skeleton className="h-4 w-full" />
        <Skeleton className="h-10 w-32" />
      </div>
    );
  }

  if (isError) {
    return (
      <div className="w-full max-w-md space-y-4 text-center">
        <p className="text-destructive">We couldn&apos;t check your vendor status.</p>
        <Button render={<Link href="/dashboard" />} variant="outline">
          Back to dashboard
        </Button>
      </div>
    );
  }

  const vendor = data?.vendor;
  const complete = vendor?.stripe_onboarding_complete;

  return (
    <div className="w-full max-w-md space-y-4 text-center">
      <h1 className="text-xl font-semibold text-foreground">
        {complete ? "You're all set! 🎉" : "Almost there"}
      </h1>
      <p className="text-sm text-muted-foreground">
        {complete
          ? `${vendor?.shop_name} is connected to Stripe and ready to receive payouts.`
          : "Stripe still needs a bit more information before your shop can go live."}
      </p>

      {complete ? (
        <Button render={<Link href="/dashboard" />}>Go to dashboard</Button>
      ) : data?.onboarding_url ? (
        <Button render={<a href={data.onboarding_url} />}>Continue Stripe setup</Button>
      ) : null}
    </div>
  );
}