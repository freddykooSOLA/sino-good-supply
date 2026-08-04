# SINO GOOD Website — Laravel 11 + Filament 3

Official website for **SINO GOOD QY Supply Chain CO LTD** (装修物料供应商, 主攻欧洲酒店).

## Stack

- Laravel 11
- FilamentPHP 3 (admin in Simplified Chinese)
- MySQL
- Tailwind CSS
- Multi-language frontend: `/en`, `/zh`, `/zh-hant`

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB in .env
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Admin: `http://localhost:8000/admin`  
Default admin (after seed): `admin@sinogood.com` / `Sinogood@2026` — change after first login.

## Deploy (SiteGround)

1. Import `~/.ssh/sinogood_siteground.pub` into SiteGround SSH Keys.
2. Create MySQL database and update remote `.env`.
3. Set Document Root to `.../sinogood/public`.
4. Edit `deploy.sh` with your `SSH_USER`, `SSH_HOST`, `SITE_PATH`.
5. Run `chmod +x deploy.sh && ./deploy.sh`.
