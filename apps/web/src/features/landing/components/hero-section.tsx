"use client";

import Link from "next/link";
import { ArrowRight, ShoppingBag, Store } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

export function HeroSection() {
  const token = useAuthStore((s) => s.token);

  return (
    <section className="relative overflow-hidden border-b border-border">
      <div className="absolute inset-0 -z-10 bg-gradient-to-b from-primary/10 via-background to-background" />
      <div className="absolute -left-24 top-24 -z-10 h-72 w-72 rounded-full bg-primary/20 blur-3xl" />
      <div className="absolute -right-24 top-52 -z-10 h-72 w-72 rounded-full bg-primary/10 blur-3xl" />

      <div className="mx-auto flex max-w-5xl flex-col items-center gap-6 px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8">
        <span className="inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-1.5 text-xs font-medium text-muted-foreground">
          <span className="h-1.5 w-1.5 rounded-full bg-primary" />
          Multi-vendor marketplace
        </span>

        <h1 className="max-w-3xl text-balance text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
          One cart. Every vendor. <span className="text-primary">Paid out instantly.</span>
        </h1>

        <p className="max-w-xl text-balance text-lg text-muted-foreground">
          ShopWave lets independent sellers open their own storefronts while shoppers check out
          once — with payments automatically split and sent straight to each vendor.
        </p>

        <div className="flex flex-col gap-3 sm:flex-row">
          <Button size="lg" render={<Link href="/products" />} className="gap-2">
            <ShoppingBag className="h-4 w-4" />
            Browse products
          </Button>
          {token ? (
            <Button size="lg" variant="outline" render={<Link href="/dashboard" />} className="gap-2">
              Go to dashboard
              <ArrowRight className="h-4 w-4" />
            </Button>
          ) : (
            <Button size="lg" variant="outline" render={<Link href="/register" />} className="gap-2">
              <Store className="h-4 w-4" />
              Start selling
            </Button>
          )}
        </div>
      </div>
    </section>
  );
}