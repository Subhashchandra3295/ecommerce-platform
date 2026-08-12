import path from "node:path";
import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // "standalone" is for the self-hosted Docker build (docker-compose) — Vercel
  // has its own bundling/tracing pipeline and this conflicts with it there.
  output: process.env.VERCEL ? undefined : "standalone",
  turbopack: {
    root: path.join(__dirname),
  },
};

export default nextConfig;
