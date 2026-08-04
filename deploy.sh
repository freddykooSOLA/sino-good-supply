#!/bin/bash
# SINO GOOD — SiteGround deployment script
# Replace SSH_USER / SSH_HOST / SITE_PATH with your SiteGround details before running.

set -euo pipefail

SSH_USER="your_siteground_username"
SSH_HOST="your_siteground_hostname"   # e.g. gXXXX.siteground.biz or ssh.siteground.com
SITE_PATH="/home/your_username/www/yourdomain.com/public_html/sinogood"
SSH_KEY="${HOME}/.ssh/sinogood_siteground"
PHP_BIN="/usr/local/bin/php8.3"
COMPOSER_BIN="/usr/local/bin/composer"

echo "==> Deploying SINO GOOD to SiteGround..."

# 1. Push latest code to GitHub
git push origin main

# 2. Pull & build on remote
ssh -i "$SSH_KEY" -o IdentitiesOnly=yes "${SSH_USER}@${SSH_HOST}" bash -s << EOF
  set -euo pipefail
  cd "${SITE_PATH}"
  git pull origin main

  ${PHP_BIN} ${COMPOSER_BIN} install --no-dev --optimize-autoloader --no-interaction

  ${PHP_BIN} artisan migrate --force
  ${PHP_BIN} artisan storage:link || true
  ${PHP_BIN} artisan config:cache
  ${PHP_BIN} artisan route:cache
  ${PHP_BIN} artisan view:cache

  chmod -R 755 storage bootstrap/cache
EOF

echo "==> Deployment complete!"
echo "Remember: Document Root must point to ${SITE_PATH}/public"
echo "Admin panel: https://your-domain.com/admin"
