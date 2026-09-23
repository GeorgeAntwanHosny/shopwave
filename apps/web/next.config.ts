import type { NextConfig } from "next";

const nextConfig: NextConfig = {
 output: 'standalone',
  typescript: {
    // Allows production builds to succeed even if there are type errors
    ignoreBuildErrors: true,
  },
  images: {
    remotePatterns: [
      { protocol: "http", hostname: "localhost", port: "8000", pathname: "/storage/**" },
      { protocol: "http", hostname: "127.0.0.1", port: "8000", pathname: "/storage/**" },
    ],
  },
};

export default nextConfig;