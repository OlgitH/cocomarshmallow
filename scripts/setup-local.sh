#!/usr/bin/env bash
# One-shot local setup: install WP, activate theme + plugins, seed starter content.
# Usage: scripts/setup-local.sh [admin_user] [admin_email] [admin_password]
set -euo pipefail
cd "$(dirname "$0")/.."

ADMIN_USER="${1:-admin}"
ADMIN_EMAIL="${2:-hello@cocomarshmallowbooks.co.uk}"
ADMIN_PASSWORD="${3:-$(openssl rand -base64 18 | tr -d '/+=' | cut -c1-20)}"
if docker compose version >/dev/null 2>&1; then DC="docker compose"; else DC="docker-compose"; fi
WP="$DC exec -T -u www-data wordpress wp"

if ! $WP core is-installed 2>/dev/null; then
  $WP core install --url="$(grep -E '^WP_HOME=' .env | cut -d= -f2- | tr -d "'\"")" --title="Cocomarshmallow Books" \
    --admin_user="$ADMIN_USER" --admin_email="$ADMIN_EMAIL" --admin_password="$ADMIN_PASSWORD" --skip-email
  echo "Admin login: $ADMIN_USER / $ADMIN_PASSWORD  (shown once, save it)"
fi

$WP plugin activate woocommerce woocommerce-gateway-stripe seo-by-rank-math
$WP plugin is-installed advanced-custom-fields-pro 2>/dev/null && $WP plugin activate advanced-custom-fields-pro || echo "ACF Pro not installed yet (add the key to auth.json and run composer update)."
$WP theme activate cocomarshmallow
$WP eval-file scripts/seed.php
