# Upstash + Pusher (Render)

Reference for optional Redis and required Pusher on Render. **App setup:** [README.md](../README.md).

## Upstash Redis (optional)

1. Create DB at [console.upstash.com](https://console.upstash.com) (region near Render).
2. Copy `rediss://…` URL into Render as `REDIS_URL`.
3. Set `REDIS_CLIENT=predis`. Roll out safely: `CACHE_STORE=redis` first, then `SESSION_DRIVER=redis` if stable.

## Pusher (required for live notifications)

1. Create a Channels app at [dashboard.pusher.com](https://dashboard.pusher.com).
2. Set in Render:

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
VITE_PUSHER_APP_KEY=        # same as PUSHER_APP_KEY
VITE_PUSHER_APP_CLUSTER=mt1
```

3. **Redeploy** after changing `VITE_*` (baked at Docker build).

## Verify

- Pusher dashboard → Debug Console: connections/events
- Two browsers: admin updates order → customer badge updates
- If auth fails: user logged in, CSRF/session OK

## Related

- [HOSTING-RENDER.md](HOSTING-RENDER.md) — Render troubleshooting
