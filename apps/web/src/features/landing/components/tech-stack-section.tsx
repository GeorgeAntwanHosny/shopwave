import { Badge } from "@/components/ui/badge";

const STACK = ["Next.js 16", "TypeScript", "Laravel 11", "PostgreSQL 16", "Redis", "Stripe Connect", "Laravel Reverb"];

const HIGHLIGHTS = [
  { label: "Split payments", detail: "One checkout, automatically divided across every vendor via Stripe Connect" },
  { label: "Atomic Redis cart", detail: "HINCRBY-based add-to-cart — race-condition-safe with zero locking" },
  { label: "Live WebSockets", detail: "Persisted + broadcast notifications via Laravel Reverb, never lost offline" },
  { label: "Full admin tooling", detail: "Refunds that correctly reverse Stripe Transfers, not just charges" },
];

export function TechStackSection() {
  return (
    <section className="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">
      <div className="mx-auto max-w-2xl text-center">
        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Engineering highlights</h2>
        <p className="mt-3 text-muted-foreground">A headless API, a decoupled frontend, and the pieces most demos skip.</p>
      </div>

      <div className="mt-8 flex flex-wrap justify-center gap-2">
        {STACK.map((tech) => (
          <Badge key={tech} variant="outline" className="px-3 py-1 text-sm">{tech}</Badge>
        ))}
      </div>

      <div className="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2">
        {HIGHLIGHTS.map((item) => (
          <div key={item.label} className="rounded-xl border border-border bg-card p-5">
            <p className="font-semibold text-card-foreground">{item.label}</p>
            <p className="mt-1 text-sm text-muted-foreground">{item.detail}</p>
          </div>
        ))}
      </div>
    </section>
  );
}