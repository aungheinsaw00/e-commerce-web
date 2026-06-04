# CarPart

E-commerce app for car parts: catalog, cart, pay-by-transfer checkout, payment proof upload, admin verification, and live notifications.

## Tech Stack

- Laravel 12, PHP 8.4, MySQL 8
- Blade, Tailwind CSS v4, Alpine.js, Vite 7
- Pusher Channels, Laravel Echo
- Docker via Laravel Sail

## Prerequisites

- Docker Desktop running (WSL2 on Windows)
- Clone the repo **inside WSL** (e.g. `~/projects/…`), not on `C:\` or `/mnt/c/`
- Composer on your machine for the first `composer install`

Node **20.19+** or **22.12+** is only needed if you run `npm` on the host. The steps below use Sail instead, so you can skip installing Node locally.

## Quick Start

1. Clone and enter the project:

```bash
git clone https://github.com/Wyco68/e-commerce-web.git
cd e-commerce-web
```

2. Copy env and install PHP packages:

```bash
cp .env.example .env
composer install
```

3. Start Sail, install frontend packages, and build assets:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
```

4. Migrate and seed:

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
```

5. Open http://localhost

## Admin Setup

Admin users come from the database seeder (not a separate command).

| Role | Email | Password |
|------|--------|----------|
| Admin | `admin@carpart.test` | `password` |
| Customer | `user@carpart.test` | `password` |

- Admin panel: `/admin` (log in as admin first)
- Re-seed anytime: `./vendor/bin/sail artisan migrate:fresh --seed`

## Testing

```bash
./vendor/bin/sail test
```

## Troubleshooting

### `npm install` or `npm ci` fails

Use Sail instead of npm on Windows or an old Node version:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
```

Still failing? Remove `node_modules` and try again:

```bash
rm -rf node_modules
./vendor/bin/sail npm ci
```

If the error says **EBADENGINE** or mentions **vite**, your Node is too old. Use Sail (above) or upgrade to Node **20.19+** or **22.12+**.

### “Vite manifest not found”

Frontend assets are not built yet (`public/build` is not in git). Run:

```bash
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
```

Then refresh http://localhost.

### Docker / Sail won’t start

Start Docker Desktop and wait until it is fully running, then run `./vendor/bin/sail up -d` again.

### 500 error or “table doesn’t exist”

Database not set up yet:

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
```

### Permission error on `storage/logs`

Run Artisan through Sail, not as root on the host:

```bash
./vendor/bin/sail artisan migrate --seed
```

## Deployment

### Render (free demo)

1. Push to GitHub and connect the repo in [Render](https://dashboard.render.com) (Blueprint reads `render.yaml`).
2. After the first deploy, set in **Environment**:
   - `APP_URL` — your `https://….onrender.com` URL (no trailing slash)
   - `APP_KEY` — from `php artisan key:generate --show` (paste as `base64:…`, no quotes)
   - `PUSHER_*` and `VITE_PUSHER_*` — from [Pusher](https://dashboard.pusher.com) (redeploy after changes)
3. Copy other vars from `.env.render.example`.
4. Redeploy. Demo logins match the table above.

Free tier sleeps when idle; first load can take 30–60s. Troubleshooting: [docs/HOSTING-RENDER.md](docs/HOSTING-RENDER.md).

### Production

MySQL, Redis, S3 uploads, queue worker, and hardening: [docs/HOSTING-PRODUCTION.md](docs/HOSTING-PRODUCTION.md).

Optional Redis on Render: [docs/UPSTASH-PUSHER.md](docs/UPSTASH-PUSHER.md).
