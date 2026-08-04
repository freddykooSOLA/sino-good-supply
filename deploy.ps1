# SINO GOOD — SiteGround deployment (PowerShell)

$ErrorActionPreference = "Stop"

$SSH_USER = "u4328-swbmojektmmv"
$SSH_HOST = "ssh.freddyk9.sg-host.com"
$SSH_PORT = "18765"
$SITE_PATH = "/home/customer/www/freddyk9.sg-host.com/sinogood"
$SSH_KEY = "$env:USERPROFILE\.ssh\sinogood_siteground"
$PHP_BIN = "/usr/local/bin/php84"
$COMPOSER_PHAR = "/usr/local/bin/composer.phar"

Write-Host "==> Deploying SINO GOOD to SiteGround..."

git push origin main

$remote = @"
set -euo pipefail
cd '$SITE_PATH'
git pull origin main
$PHP_BIN $COMPOSER_PHAR install --no-dev --optimize-autoloader --no-interaction
$PHP_BIN artisan migrate --force
$PHP_BIN artisan storage:link || true
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
chmod -R 775 storage bootstrap/cache
"@

$remote | ssh -i $SSH_KEY -o IdentitiesOnly=yes -p $SSH_PORT "${SSH_USER}@${SSH_HOST}" bash

Write-Host "==> Deployment complete!"
Write-Host "Document Root must point to: $SITE_PATH/public"
Write-Host "Site: https://freddyk9.sg-host.com"
Write-Host "Admin: https://freddyk9.sg-host.com/admin"
