import Link from "next/link";
import { ArrowRight, Check } from "lucide-react";
import { Button } from "@/components/ui/button";

const SHOPPER_POINTS = [
  "Browse a unified catalog from every vendor at once",
  "One checkout, even when your cart spans multiple sellers",
  "Track orders and reviews in real time",
];

const VENDOR_POINTS = [
  "Open your shop with Stripe Connect onboarding",
  "Get paid automatically, minus a transparent platform fee",
  "Manage inventory, coupons, and orders from one dashboard",
];

export function AudienceSection() {
  return (
    <section className="border-y border-border bg-muted/30">
      <div className="mx-auto grid max-w-5xl gap-8 px-4 py-20 sm:px-6 md:grid-cols-2 lg:px-8">
        <div className="rounded-xl border border-border bg-card p-6 sm:p-8">
          <h3 className="text-xl font-semibold text-card-foreground">For shoppers</h3>
          <ul className="mt-4 space-y-3">
            {SHOPPER_POINTS.map((point) => (
              <li key={point} className="flex items-start gap-2 text-sm text-muted-foreground">
                <Check className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                {point}
              </li>
            ))}
          </ul>
          <Button variant="outline" className="mt-6 gap-2" render={<Link href="/products" />}>
            Start browsing
            <ArrowRight className="h-4 w-4" />
          </Button>
        </div>

        <div className="rounded-xl border border-border bg-card p-6 sm:p-8">
          <h3 className="text-xl font-semibold text-card-foreground">For vendors</h3>
          <ul className="mt-4 space-y-3">
            {VENDOR_POINTS.map((point) => (
              <li key={point} className="flex items-start gap-2 text-sm text-muted-foreground">
                <Check className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                {point}
              </li>
            ))}
          </ul>
          <Button variant="outline" className="mt-6 gap-2" render={<Link href="/register" />}>
            Become a vendor
            <ArrowRight className="h-4 w-4" />
          </Button>
        </div>
      </div>
    </section>
  );
}