import Link from "next/link";
import { ThemeToggle } from "@/components/theme-toggle";
import { CartButton } from "@/features/cart/components/cart-button";

export function SiteHeader() {
  return (
    <header className="sticky top-0 z-40 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="mx-auto flex h-14 max-w-5xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div className="flex items-center gap-6">
          <Link href="/" className="text-sm font-semibold text-foreground sm:text-base">ShopWave</Link>
          <Link href="/products" className="text-sm text-muted-foreground hover:text-foreground">Browse</Link>
        </div>
        <div className="flex items-center gap-2">
          <CartButton />
          <ThemeToggle />
        </div>
      </div>
    </header>
  );
}