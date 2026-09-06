import Link from "next/link";
import { Package } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

interface VendorStatusCardProps {
  shopName: string;
  onboardingComplete: boolean;
}

export function VendorStatusCard({ shopName, onboardingComplete }: VendorStatusCardProps) {
  return (
    <div className="rounded-lg border border-border bg-card p-6 shadow-sm">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 className="text-lg font-semibold text-card-foreground">{shopName}</h2>
          <p className="text-sm text-muted-foreground">Your vendor shop</p>
        </div>
        <Badge variant={onboardingComplete ? "default" : "secondary"}>
          {onboardingComplete ? "Stripe connected" : "Onboarding incomplete"}
        </Badge>
      </div>

      {!onboardingComplete && (
        <Link
          href="/vendor/onboarding/complete"
          className="mt-4 inline-block text-sm font-medium text-primary underline-offset-4 hover:underline"
        >
          Finish connecting Stripe →
        </Link>
      )}

      <div className="mt-5 border-t border-border pt-4">
        <Button render={<Link href="/vendor/products" />} className="w-full sm:w-auto">
          <Package className="mr-2 h-4 w-4" />
          Manage products
        </Button>
        {!onboardingComplete && (
          <p className="mt-2 text-xs text-muted-foreground">
            You can view your products now, but you&apos;ll need to finish Stripe onboarding before adding new ones.
          </p>
        )}
      </div>
    </div>
  );
}