import { NextRequest, NextResponse } from "next/server";

const PROTECTED_PATHS = ["/dashboard","/become-a-vendor", "/vendor", "/checkout", "/orders"];
const AUTH_PATHS = ["/login", "/register"];

export function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const token = request.cookies.get("shopwave_token");

  const isProtected = PROTECTED_PATHS.some((path) => pathname.startsWith(path));

  const isAuthRoute = AUTH_PATHS.some((path) => pathname.startsWith(path));

  if (isProtected && !token) {
    return NextResponse.redirect(new URL("/login", request.url));
  }

  if (isAuthRoute && token) {
    return NextResponse.redirect(new URL("/dashboard", request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/dashboard/:path*", "/become-a-vendor/:path*", "/vendor/:path*", "/checkout/:path*", "/orders/:path*", "/login", "/register"],
};