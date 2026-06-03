# CarPart — Architecture

## Overview

Monolithic Laravel app: storefront, cart, checkout (pay-by-transfer), admin dashboard, inventory, refunds, notifications.

**Users:** customers (browse/buy) and admins (catalog, orders, payments).

## System Flow

```
HTTP → Middleware (auth / is_admin) → Controller → Service → MySQL
Event → Pusher → Echo / Alpine.js (notifications)
```

## Core Domains

| Area | Notes |
|------|--------|
| Catalog | Products, categories, brands, variants |
| Inventory | Per-variant stock; `lockForUpdate()` in transactions |
| Cart / checkout | DB cart; pay-by-transfer + proof upload |
| Payments | SHA-256 hash blocks duplicate proof files |
| Notifications | DB + broadcast on `user.{id}` (Pusher) |
| Auth | Session-based; Policies + `is_admin` middleware |

## Database (main tables)

`users`, `products`, `categories`, `brands`, `product_variants`, `inventories`, `inventory_movements`, `carts`, `orders`, `payments`, `payment_methods`, `notifications`, `coupons`, `refund_requests`, `order_status_histories`

Relationships: orders → order items; products → variants → inventory.

## Realtime

- **Channel:** private `user.{id}`
- **Stack:** Laravel events → Pusher → Echo; UI state in Alpine.js
- **Offline:** notifications stored in DB; loaded on page load

## Security

- Form requests for input validation
- Policies for order/cart access
- Admin routes: `auth` + `is_admin`
- No Postgres RLS (app-level authorization only)

## Limits

- Pay-by-transfer only (no Stripe/PayPal)
- Storefront is Blade (no full React SPA)

## Future (optional)

- Payment gateways, Turnstile on checkout, SPA storefront
