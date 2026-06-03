# CarPart

E-commerce app for car parts: catalog, cart, pay-by-transfer checkout, payment proof upload, admin verification, and live notifications.

## Tech Stack

- Laravel 11, PHP 8.4, MySQL 8
- Blade, Tailwind CSS v4, Alpine.js, Vite
- Pusher Channels, Laravel Echo
- Docker via Laravel Sail

## Quick Start

Use WSL and keep the project on the Linux filesystem.

1. Clone and enter the project:

```bash
git clone https://github.com/Wyco68/e-commerce-web.git
cd e-commerce-web
```

2. Copy env and install dependencies:

```bash
cp .env.example .env
composer install
npm install
```

3. Start Sail and build assets:

```bash
./vendor/bin/sail up -d
npm run build
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
