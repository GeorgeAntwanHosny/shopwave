import { Providers } from "./providers";
import { SiteHeader } from "@/components/site-header";
import { CartSheet } from "@/features/cart/components/cart-sheet";
import "./globals.css";

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body suppressHydrationWarning>
        <Providers>
          <SiteHeader />
          {children}
          <CartSheet />
        </Providers>
      </body>
    </html>
  );
}