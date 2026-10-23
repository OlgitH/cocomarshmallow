# Cocomarshmallow Books

Bedrock + Docker WordPress site (WooCommerce, ACF Pro, Rank Math, Stripe) with the custom theme in `web/app/themes/cocomarshmallow`.

## Local
```
docker-compose up -d --build        # or: docker compose up -d --build
scripts/setup-local.sh              # installs WP, activates plugins/theme, seeds placeholder content
```
Site: http://localhost:8088 (admin: /wp/wp-admin). The port is set in `docker-compose.override.yml`; keep `WP_HOME` in `.env` in sync.

## ACF Pro
Put the licence key in `auth.json` (gitignored), then:
```
composer require wpengine/advanced-custom-fields-pro
```
Fields are registered in code (`inc/acf-fields.php`). Until ACF Pro is installed, simple meta boxes stand in for them.

## Stripe / email
Set the `STRIPE_*` and `SMTP_*` values in `.env` (see `.env.example`). They override the plugin settings via `web/app/mu-plugins/cm-integrations.php`.

## Content
- **Books**: WooCommerce products. Tick "Feature in homepage hero" for the hero book (else the newest is used); mark products "featured" for the homepage grid.
- **Children's activities**: Activities > Add new, upload the file, it appears on the activities page with a download button.
- **Social links / contact email**: Site settings (ACF options page).
