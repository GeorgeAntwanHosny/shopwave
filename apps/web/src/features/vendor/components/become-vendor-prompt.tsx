import Link from "next/link";
import { Button } from "@/components/ui/button";

export function BecomeVendorPrompt() {
  return (
    <div className="rounded-lg border border-border bg-card p-6 shadow-sm">
      <h2 className="text-lg font-semibold text-card-foreground">Ready to sell?</h2>
      <p className="mt-1 text-sm text-muted-foreground">
        Open your own shop and connect Stripe to start receiving payouts.
      </p>
      <Button render={<Link href="/become-a-vendor" />} className="mt-4">
        Become a vendor
      </Button>
    </div>
  );
}