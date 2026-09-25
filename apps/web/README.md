# ShopWave Web (Next.js)

The frontend for [ShopWave](../../README.md) — consumes the Laravel API in `apps/api` over REST + a Reverb WebSocket, no server-rendered coupling to the backend.

## Stack

Next.js 16 (App Router) · TypeScript · Tailwind CSS + shadcn/ui · TanStack Query · Zustand · react-hook-form + zod · Laravel Echo + Pusher-js · Recharts · dnd-kit · next-themes

## Running

**Docker (recommended)** — from the repo root: `make up` (see [root README](../../README.md#-quick-start-docker)). Serves at http://localhost:3000.

**Native (no Docker)** — requires the API running separately (Docker or native, see [`apps/api/README.md`](../api/README.md)):
```bash
npm install
cp .env.local.example .env.local
npm run dev          # http://localhost:3000
```

## Environment

| Variable | Purpose |
|---|---|
| `NEXT_PUBLIC_API_URL` | Laravel API base URL |
| `NEXT_PUBLIC_REVERB_*` | Reverb WebSocket connection (host/port/scheme/app key) — must be the browser-facing host, never a Docker service name |
| `NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY` | Your own Stripe test-mode publishable key |

## UI/UX Standards

Applies to every component in this app, no exceptions:
- **Responsive by default** — mobile-first, checked at mobile/tablet/desktop.
- **Dark & light mode** — via `next-themes` and shadcn/ui's theme-aware tokens; nothing hardcodes a light-only color.
- **Skeleton loading states** — every data-fetching view shows a skeleton matching its final layout, never a blank screen or bare spinner.
- **Toast feedback** — validation/API errors and success confirmations via `sonner`, never raw inline text alone.
- **Dual-layer validation** — every form validates client-side (`react-hook-form` + `zod`) for instant feedback and is re-validated by the backend Form Request, the real security boundary.
- **Explicit mutation states** — idle, loading (disabled submit + spinner), error (toast + inline field errors), success (toast + actual UI update).

## 
```text
src/
├── app/ # App Router pages & layouts (root ThemeProvider in layout.tsx)
│ └── admin/ # Admin panel — role-gated, own sub-navigation
├── components/ # UI & shadcn components
├── features/ # Domain logic (cart, checkout, vendor, reviews, notifications, admin)
├── lib/ # TanStack Query, Zustand, and Echo/Reverb client instances
├── lib/validations/ # zod schemas, one per form
└── types/ # TypeScript types, incl. ApiResponse<T>
```
See the [root README](../../README.md) for the full architecture and feature list.