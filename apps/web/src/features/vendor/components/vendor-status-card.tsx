import { Badge } from "@/components/ui/badge";
import Link from "next/link";

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
    </div>
  );
}