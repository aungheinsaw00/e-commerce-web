# Render Hosting Notes

Portfolio demo: Docker Web Service, SQLite, `render.yaml` Blueprint. **Setup steps are in [README.md](../README.md).**

## Repo files

| File | Role |
|------|------|
| `render.yaml` | Blueprint |
| `.env.render.example` | Env template |
| `Dockerfile` | Build (Composer + Vite) |
| `docker/render/start.sh` | Migrate, seed, serve |

## Demo logins

| Role | Email | Password |
|------|--------|----------|
| Admin | `admin@carpart.test` | `password` |
| Customer | `user@carpart.test` | `password` |

## Free tier

- Sleeps after ~15 min idle; cold start ~30–60s
- SQLite + seed on each container start when `APP_DEMO_MODE=true`
- Uploads on local disk may not survive redeploy

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 502 / build fail | Check logs; set `APP_KEY` and `APP_URL` |
| 500 everywhere | `APP_KEY=base64:…`, correct `APP_URL`, redeploy |
| 419 on login | `SESSION_SECURE_COOKIE=false`, `SESSION_DRIVER=cookie`, clear cookies |
| No notifications | Set all `PUSHER_*` + `VITE_PUSHER_*`, redeploy (Vite bakes at build) |
| No products | Redeploy or `php artisan db:seed --class=RenderDemoSeeder --force` in Shell |
| Unstyled UI | Redeploy; hard-refresh; check `npm run build` in deploy logs |

## Related

- [UPSTASH-PUSHER.md](UPSTASH-PUSHER.md) — optional Redis + Pusher env names
- [HOSTING-PRODUCTION.md](HOSTING-PRODUCTION.md) — full production layout
