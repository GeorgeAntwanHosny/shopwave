import { OnboardingStatus } from "@/features/vendor/components/onboarding-status";

export default function VendorOnboardingCompletePage() {
  return (
    <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center bg-background px-4">
      <OnboardingStatus />
    </div>
  );
}