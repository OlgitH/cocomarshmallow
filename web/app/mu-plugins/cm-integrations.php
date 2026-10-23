<?php
/**
 * Plugin Name: Cocomarshmallow Integrations
 * Description: Wires Stripe keys and SMTP settings from .env into WooCommerce Stripe Gateway and PHPMailer.
 */

defined('ABSPATH') || exit;

/**
 * Stripe: environment values override whatever is stored in the database,
 * so keys never live in the DB or in version control.
 */
add_filter('option_woocommerce_stripe_settings', static function ($settings) {
    if (!is_array($settings)) {
        $settings = [];
    }
    $map = [
        'testmode'             => 'STRIPE_TEST_MODE',
        'test_publishable_key' => 'STRIPE_TEST_PUBLISHABLE_KEY',
        'test_secret_key'      => 'STRIPE_TEST_SECRET_KEY',
        'test_webhook_secret'  => 'STRIPE_TEST_WEBHOOK_SECRET',
        'publishable_key'      => 'STRIPE_PUBLISHABLE_KEY',
        'secret_key'           => 'STRIPE_SECRET_KEY',
        'webhook_secret'       => 'STRIPE_WEBHOOK_SECRET',
    ];
    foreach ($map as $setting => $constant) {
        if (defined($constant)) {
            $settings[$setting] = constant($constant);
        }
    }
    return $settings;
});

/** SMTP via PHPMailer when SMTP_HOST is set. */
add_action('phpmailer_init', static function ($mailer) {
    if (!defined('SMTP_HOST')) {
        return;
    }
    $mailer->isSMTP();
    $mailer->Host       = SMTP_HOST;
    $mailer->Port       = defined('SMTP_PORT') ? (int) SMTP_PORT : 587;
    $mailer->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
    if (defined('SMTP_USER')) {
        $mailer->SMTPAuth = true;
        $mailer->Username = SMTP_USER;
        $mailer->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
    }
});

add_filter('wp_mail_from', static fn ($from) => defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : $from);
add_filter('wp_mail_from_name', static fn ($name) => defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : $name);
