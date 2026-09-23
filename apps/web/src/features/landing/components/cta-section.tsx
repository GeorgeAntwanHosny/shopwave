"use client";

import Link from "next/link";
import { ArrowRight } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

export function CtaSection() {
  const token = useAuthStore((s) => s.token);

  return (
    <section className="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">
      <div className="rounded-2xl border border-border bg-gradient-to-br from-primary/10 via-card to-card p-8 text-center sm:p-12">
        <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
          {token ? "Pick up right where you left off" : "Ready to jump in?"}
        </h2>
        <p className="mx-auto mt-3 max-w-md text-muted-foreground">
          {token
            ? "Your dashboard, orders, and notifications are all waiting for you."
            : "Create an account in seconds — browse as a shopper, or open your own shop."}
        </p>
        <div className="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
          {token ? (
            <Button size="lg" render={<Link href="/dashboard" />} className="gap-2">
              Go to dashboard
              <ArrowRight className="h-4 w-4" />
            </Button>
          ) : (
            <>
              <Button size="lg" render={<Link href="/register" />} className="gap-2">
                Create free account
                <ArrowRight className="h-4 w-4" />
              </Button>
              <Button size="lg" variant="outline" render={<Link href="/login" />}>
                Sign in
              </Button>
            </>
          )}
        </div>
      </div>
    </section>
  );
}