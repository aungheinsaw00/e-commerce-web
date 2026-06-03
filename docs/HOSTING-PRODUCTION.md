# Production Hosting

For real traffic: managed MySQL, Redis, S3-compatible storage, queue worker, HTTPS.  
**Local setup:** [README.md](../README.md). **Render demo:** [HOSTING-RENDER.md](HOSTING-RENDER.md).

## Architecture

| Service | Role |
|---------|------|
| Web | Laravel Docker app, health `/up` |
| Worker | `php artisan queue:work redis` |
| MySQL | Primary database |
| Redis | Sessions, cache, queues (`REDIS_URL`, not REST token) |
| S3 / R2 | `FILESYSTEM_DISK=s3` for payment proofs |
| Pusher | `BROADCAST_CONNECTION=pusher` |

Keep app, DB, and Redis in the same region.

## Pre-deploy

- [ ] Production-only `APP_KEY`, DB, Redis, Pusher credentials
- [ ] `APP_DEBUG=false`, `APP_DEMO_MODE=false`
- [ ] `SESSION_SECURE_COOKIE=true` on HTTPS domain
- [ ] DB backups defined
- [ ] `VITE_*` set before image build

## Deploy flow

1. Set env vars in the platform (see `.env.example` + production overrides).
2. Deploy web + worker images.
3. `php artisan migrate --force` (no demo seeders).
4. Smoke test: `/up`, login, cart, checkout, proof upload, admin verify, Pusher badge, queue draining.

## Smoke test

- [ ] `/up` → 200
- [ ] Login / logout
- [ ] Checkout + proof upload (S3)
- [ ] Admin payment verify + realtime notification
- [ ] Worker processes jobs (no growing backlog)

## Rollback

1. Redeploy previous image  
2. Restore DB only if migration was destructive  
3. Re-run smoke tests

## Related

- [UPSTASH-PUSHER.md](UPSTASH-PUSHER.md) — Redis + Pusher on Render
