import { HeroSection } from "@/features/landing/components/hero-section";
import { FeaturesSection } from "@/features/landing/components/features-section";
import { AudienceSection } from "@/features/landing/components/audience-section";
import { TechStackSection } from "@/features/landing/components/tech-stack-section";
import { CtaSection } from "@/features/landing/components/cta-section";
import { LandingFooter } from "@/features/landing/components/landing-footer";

export default function HomePage() {
  return (
    <div>
      <HeroSection />
      <FeaturesSection />
      <AudienceSection />
      <TechStackSection />
      <CtaSection />
      <LandingFooter />
    </div>
  );
}