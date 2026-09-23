import Link from "next/link";

export function LandingFooter() {
  return (
    <footer className="border-t border-border">
      <div className="mx-auto flex max-w-5xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-muted-foreground sm:flex-row sm:px-6 lg:px-8">
        <p>© {new Date().getFullYear()} ShopWave. Built as a full-stack portfolio project.</p>
        <div className="flex gap-4">
          <Link href="/products" className="hover:text-foreground">Browse</Link>
          <Link href="/login" className="hover:text-foreground">Login</Link>
          <Link href="/register" className="hover:text-foreground">Register</Link>
        </div>
      </div>
    </footer>
  );
}