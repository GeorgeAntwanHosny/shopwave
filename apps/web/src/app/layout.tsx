import { Providers } from "./providers";
import "./globals.css";
import { SiteHeader } from "@/components/site-header";

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      {/* Add suppressHydrationWarning to the body tag */}
      <body suppressHydrationWarning>
        <Providers>
          <SiteHeader />
          {children}</Providers>
      </body>
    </html>
  );
}