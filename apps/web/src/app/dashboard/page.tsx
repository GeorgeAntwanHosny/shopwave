"use client";

import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { useMe } from "@/features/auth/hooks/useMe";
import { useLogout } from "@/features/auth/hooks/useLogout";
import { BecomeVendorPrompt } from "@/features/vendor/components/become-vendor-prompt";
import { VendorStatusCard } from "@/features/vendor/components/vendor-status-card";
import { DashboardPreview } from "@/features/vendor/dashboard/components/dashboard-preview";

export default function DashboardPage() {
  const router = useRouter();
  const { data, isLoading, isError } = useMe();
  const { mutate: logout, isPending: isLoggingOut } = useLogout();

  function handleLogout() {
    logout(undefined, {
      onSuccess: () => {
        toast.success("Logged out successfully.");
        router.push("/login");
      },
      onError: () => toast.error("Something went wrong logging out."),
    });
  }

  if (isLoading) {
    return (
      <div className="mx-auto max-w-2xl space-y-4 p-4 sm:p-6 lg:p-8">
        <Skeleton className="h-8 w-48" />
        <Skeleton className="h-4 w-64" />
        <Skeleton className="h-32 w-full" />
        <Skeleton className="h-10 w-24" />
      </div>
    );
  }

  if (isError) {
    return (
      <div className="mx-auto max-w-2xl p-4 text-center sm:p-6 lg:p-8">
        <p className="text-destructive">Could not load your profile. Please log in again.</p>
        <Button className="mt-4" onClick={() => router.push("/login")}>
          Go to login
        </Button>
      </div>
    );
  }

  const vendor = data?.user.vendor;

  return (
    <div className="mx-auto flex max-w-2xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
      <div>
        <h1 className="text-2xl font-semibold text-foreground">Welcome, {data?.user.name}</h1>
        <p className="text-muted-foreground">{data?.user.email}</p>
      </div>

      {vendor ? (
        <>
          <DashboardPreview />
          <VendorStatusCard shopName={vendor.shop_name} onboardingComplete={vendor.stripe_onboarding_complete} />
        </>
      ) : (
        <BecomeVendorPrompt />
      )}

      <Button variant="outline" className="w-fit" onClick={handleLogout} disabled={isLoggingOut}>
        {isLoggingOut ? "Logging out..." : "Logout"}
      </Button>
    </div>
  );
}