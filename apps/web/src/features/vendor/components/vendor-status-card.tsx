import Link from "next/link";
import { Package, Tag, ClipboardList } from "lucide-react";
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

      <div className="mt-5 flex flex-col gap-2 border-t border-border pt-4 sm:flex-row sm:flex-wrap">
        <Button render={<Link href="/vendor/products" />} className="w-full sm:w-auto">
          <Package className="mr-2 h-4 w-4" />
          Manage products
        </Button>
        <Button render={<Link href="/vendor/coupons" />} variant="outline" className="w-full sm:w-auto">
          <Tag className="mr-2 h-4 w-4" />
          Manage coupons
        </Button>
        <Button render={<Link href="/vendor/orders" />} variant="outline" className="w-full sm:w-auto">
          <ClipboardList className="mr-2 h-4 w-4" />
          Received orders
        </Button>
      </div>
      {!onboardingComplete && (
        <p className="mt-2 text-xs text-muted-foreground">
          You can view these now, but you&apos;ll need to finish Stripe onboarding before adding new products or coupons.
        </p>
      )}
    </div>
  );
}