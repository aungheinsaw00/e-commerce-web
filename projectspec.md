# Project Specification

## 1. Overview
- **Project name:** CarPart E-Commerce Platform
- **Purpose:** A scalable e-commerce system adapted from the "book-order-platform" architecture, built on Laravel to handle physical/digital products with pay-by-transfer flows.
- **Core features:** 
  - Product variant and robust inventory management.
  - Pay-by-transfer checkout with payment proof upload and duplicate (SHA-256) detection.
  - Admin dashboard for manual payment verification and order management.
  - DB-backed shopping cart.

## 2. Architecture
- **High-level architecture:** Monolithic backend (Laravel) ready to serve either Blade views or act as an API for a frontend SPA.
- **Folder structure:** Standard Laravel 11 structure.
  - `app/Models`: Rich domain models with relationships.
  - `app/Services`: Dedicated business logic (Cart, Inventory, Order, Payment) to keep controllers thin.
  - `app/Http/Controllers`: Separated into `Admin` and public spaces.
  - `tests/Feature`: Comprehensive integration testing for services and controllers.
- **Data flow:** Request → Middleware (Auth/Admin) → Controller → Service (ACID Transactions) → Database.
- **Rendering strategy:** Currently Server-Side Rendered (SSR) via Blade stubs, designed to be swapped for a CSR React/Vite SPA if required.

## 3. Tech Stack
- **Frameworks:** Laravel 11
- **Backend/runtime:** PHP 8.4
- **Database:** MySQL 8
- **Caching/Session:** Redis
- **Containerization:** Docker (Laravel Sail)

## 4. Implementation Status (Against "book-order-platform" plan)

### ✅ What has been implemented
- **Database & Models:** Full normalization (Products, Variants, Inventory, Orders, Payments, Cart).
- **Pay-by-Transfer Flow:** Order generation mapped to manual payment proof uploads.
- **Payment Proof Verification:** Implemented with SHA-256 hash checking to prevent duplicate proof uploads.
- **Robust Inventory:** `InventoryService` using ACID-compliant `lockForUpdate()` transactions to prevent overselling.
- **Admin System:** Secured via `is_admin` middleware and Policies for reviewing payments and orders.
- **Redis Integration:** Configured for sessions and caching.
- **Automated Tests:** 38 PHPUnit tests covering checkout, inventory locks, and admin authorization.

### ❌ What is left (Pending)
- **Vite React Storefront:** Currently using Blade view stubs. The React SPA (React Router, TanStack Query) has not been integrated yet.
- **Cloudflare Turnstile & Honeypot:** Not yet integrated into the checkout submission form.
- **Magic Links & Receipt Codes:** 8-character receipt codes and opaque magic links for order tracking are missing.
- **Gated Downloads:** Private object storage with short-lived signed URLs for digital products/books has not been implemented.
- **Order Kill-Switch:** Emergency toggle to stop new orders is not implemented.
