import { BecomeVendorCard } from "@/features/vendor/components/become-vendor-card";

export default function BecomeAVendorPage() {
  return (
    <div className="flex min-h-[calc(100vh-3.5rem)] items-center justify-center bg-background px-4 py-12 sm:px-6 lg:px-8">
      <div className="w-full max-w-md">
        <BecomeVendorCard />
      </div>
    </div>
  );
}