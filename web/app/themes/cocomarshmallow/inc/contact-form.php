<?php
/** Contact form: nonce + honeypot + server-side validation, then wp_mail(). */
defined('ABSPATH') || exit;

function cm_contact_recipient(): string
{
    if (defined('CONTACT_FORM_RECIPIENT')) {
        return CONTACT_FORM_RECIPIENT;
    }
    return cm_field('contact_email', 'option') ?: get_option('admin_email');
}

function cm_handle_contact(): void
{
    $back = wp_get_referer() ?: home_url('/contact/');
    $back = remove_query_arg(['cm_sent', 'cm_error'], $back);

    if (!isset($_POST['cm_contact_nonce']) || !wp_verify_nonce($_POST['cm_contact_nonce'], 'cm_contact')) {
        wp_safe_redirect(add_query_arg('cm_error', 'nonce', $back) . '#contact-form');
        exit;
    }
    // Honeypot: bots fill the hidden field; pretend success.
    if (!empty($_POST['cm_website'])) {
        wp_safe_redirect(add_query_arg('cm_sent', '1', $back) . '#contact-form');
        exit;
    }

    $name    = sanitize_text_field(wp_unslash($_POST['cm_name'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['cm_email'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['cm_message'] ?? ''));

    if ($name === '' || !is_email($email) || $message === '') {
        wp_safe_redirect(add_query_arg('cm_error', 'invalid', $back) . '#contact-form');
        exit;
    }

    $sent = wp_mail(
        cm_contact_recipient(),
        sprintf('[%s] New message from %s', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), $name),
        "Name: {$name}\nEmail: {$email}\n\n{$message}",
        ['Reply-To: ' . $name . ' <' . $email . '>']
    );

    wp_safe_redirect(add_query_arg($sent ? 'cm_sent' : 'cm_error', $sent ? '1' : 'mail', $back) . '#contact-form');
    exit;
}
add_action('admin_post_nopriv_cm_contact', 'cm_handle_contact');
add_action('admin_post_cm_contact', 'cm_handle_contact');
