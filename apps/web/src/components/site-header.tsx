"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { LayoutDashboard, LogOut, Menu } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet";
import { ThemeToggle } from "@/components/theme-toggle";
import { CartButton } from "@/features/cart/components/cart-button";
import { NotificationBell } from "@/features/notifications/components/notification-bell";
import { useMe } from "@/features/auth/hooks/useMe";
import { useLogout } from "@/features/auth/hooks/useLogout";
import { useAuthStore } from "@/features/auth/store/useAuthStore";

interface NavLink {
  href: string;
  label: string;
}

export function SiteHeader() {
  const token = useAuthStore((s) => s.token);
  const { data } = useMe();
  const { mutate: logout } = useLogout();
  const router = useRouter();
  const [mobileOpen, setMobileOpen] = useState(false);

  const isLoggedIn = !!token;
  const isVendor = !!data?.user.vendor;

  const navLinks: NavLink[] = [
    { href: "/products", label: "Browse" },
    ...(isLoggedIn
      ? isVendor
        ? [
            { href: "/vendor/products", label: "My Products" },
            { href: "/vendor/orders", label: "Received Orders" },
          ]
        : [{ href: "/orders", label: "Orders" }]
      : []),
    ...(data?.user.is_admin ? [{ href: "/admin", label: "Admin" }] : []),
  ];

  function handleLogout() {
    logout(undefined, {
      onSuccess: () => {
        toast.success("Logged out successfully.");
        router.push("/login");
      },
      onError: () => toast.error("Something went wrong logging out."),
    });
    setMobileOpen(false);
  }

  return (
    <header className="sticky top-0 z-40 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="mx-auto flex h-14 max-w-5xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div className="flex items-center gap-6">
          <Link href="/" className="text-sm font-semibold text-foreground sm:text-base">
            ShopWave
          </Link>
          <nav className="hidden items-center gap-5 sm:flex">
            {navLinks.map((link) => (
              <Link key={link.href} href={link.href} className="text-sm text-muted-foreground hover:text-foreground">
                {link.label}
              </Link>
            ))}
          </nav>
        </div>

        <div className="flex items-center gap-2">
          {isLoggedIn && <NotificationBell />}
          <CartButton />
          <ThemeToggle />

          <div className="hidden items-center gap-2 sm:flex">
            {isLoggedIn ? (
              <>
                <Button render={<Link href="/dashboard" />} variant="outline" size="icon" aria-label="Dashboard">
                  <LayoutDashboard className="h-4 w-4" />
                </Button>
                <Button variant="outline" size="icon" onClick={handleLogout} aria-label="Logout">
                  <LogOut className="h-4 w-4" />
                </Button>
              </>
            ) : (
              <Button render={<Link href="/login" />} variant="outline" size="sm">
                Login
              </Button>
            )}
          </div>

          <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
            <SheetTrigger asChild>
              <Button variant="outline" size="icon" className="sm:hidden" aria-label="Open menu">
                <Menu className="h-4 w-4" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" className="w-64">
              <SheetHeader>
                <SheetTitle>Menu</SheetTitle>
              </SheetHeader>
              <nav className="mt-6 flex flex-col gap-4">
                {navLinks.map((link) => (
                  <Link
                    key={link.href}
                    href={link.href}
                    className="text-sm text-foreground"
                    onClick={() => setMobileOpen(false)}
                  >
                    {link.label}
                  </Link>
                ))}
                {isLoggedIn ? (
                  <>
                    <Link href="/dashboard" className="text-sm text-foreground" onClick={() => setMobileOpen(false)}>
                      Dashboard
                    </Link>
                    <button onClick={handleLogout} className="text-left text-sm text-destructive">
                      Logout
                    </button>
                  </>
                ) : (
                  <Link href="/login" className="text-sm text-foreground" onClick={() => setMobileOpen(false)}>
                    Login
                  </Link>
                )}
              </nav>
            </SheetContent>
          </Sheet>
        </div>
      </div>
    </header>
  );
}