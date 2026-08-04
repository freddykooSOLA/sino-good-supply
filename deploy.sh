#!/bin/bash
# SINO GOOD — SiteGround deployment script

set -euo pipefail

SSH_USER="u4328-swbmojektmmv"
SSH_HOST="ssh.freddyk9.sg-host.com"
SSH_PORT="18765"
SITE_PATH="/home/customer/www/freddyk9.sg-host.com/sinogood"
SSH_KEY="${HOME}/.ssh/sinogood_siteground"
PHP_BIN="/usr/local/bin/php84"
COMPOSER_PHAR="/usr/local/bin/composer.phar"

echo "==> Deploying SINO GOOD to SiteGround..."

git push origin main

ssh -i "$SSH_KEY" -o IdentitiesOnly=yes -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" bash -s << EOF
  set -euo pipefail
  cd "${SITE_PATH}"
  git pull origin main

  ${PHP_BIN} ${COMPOSER_PHAR} install --no-dev --optimize-autoloader --no-interaction

  ${PHP_BIN} artisan migrate --force
  ${PHP_BIN} artisan storage:link || true
  ${PHP_BIN} artisan config:cache
  ${PHP_BIN} artisan route:cache
  ${PHP_BIN} artisan view:cache

  chmod -R 775 storage bootstrap/cache
EOF

echo "==> Deployment complete!"
echo "Document Root must point to: ${SITE_PATH}/public"
echo "Site: https://freddyk9.sg-host.com"
echo "Admin: https://freddyk9.sg-host.com/admin"
