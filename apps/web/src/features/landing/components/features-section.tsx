import { Bell, CreditCard, LayoutDashboard, Search, ShieldCheck, ShoppingCart, Star, Store } from "lucide-react";

const FEATURES = [
  { icon: Store, title: "Multi-vendor storefronts", description: "Every seller gets their own shop, products, and coupons — all inside one unified catalog." },
  { icon: ShoppingCart, title: "One cart, every vendor", description: "Shoppers add items from as many vendors as they like and check out exactly once." },
  { icon: CreditCard, title: "Split payments", description: "A single charge is automatically divided and transferred to each vendor via Stripe Connect." },
  { icon: Bell, title: "Real-time updates", description: "Vendors and customers get live notifications the moment an order, review, or status changes." },
  { icon: Star, title: "Verified reviews", description: "Only customers who actually received an item can leave a rating — no fake reviews." },
  { icon: LayoutDashboard, title: "Vendor analytics", description: "Revenue charts, low-stock alerts, and per-product performance, updated in real time." },
  { icon: Search, title: "Fast, flexible search", description: "Filter by category, price, rating, and stock without ever feeling sluggish." },
  { icon: ShieldCheck, title: "Admin oversight", description: "Moderation tools and a full dispute-resolution workflow keep the marketplace healthy." },
];

export function FeaturesSection() {
  return (
    <section className="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">
      <div className="mx-auto max-w-2xl text-center">
        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
          Everything a marketplace needs
        </h2>
        <p className="mt-3 text-muted-foreground">
          Built as a complete, production-shaped platform — not a demo with the hard parts skipped.
        </p>
      </div>

      <div className="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        {FEATURES.map((feature) => (
          <div key={feature.title} className="rounded-xl border border-border bg-card p-5 transition-shadow hover:shadow-md">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <feature.icon className="h-5 w-5" />
            </div>
            <h3 className="mt-4 font-semibold text-card-foreground">{feature.title}</h3>
            <p className="mt-1.5 text-sm text-muted-foreground">{feature.description}</p>
          </div>
        ))}
      </div>
    </section>
  );
}