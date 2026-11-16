<?php
/**
 * Downloads gate: visitors give their name + email before getting a file.
 * Details are kept as private "Download leads" in the admin, and (only if they tick the box)
 * added to the EmailOctopus mailing list. Note this is a "soft" gate: the files themselves are normal uploads.
 */
defined('ABSPATH') || exit;

const CM_DL_COOKIE = 'cm_dl';

add_action('init', static function () {
    register_post_type('cm_lead', [
        'labels'       => ['name' => __('Download leads', 'cocomarshmallow'), 'singular_name' => __('Download lead', 'cocomarshmallow'), 'menu_name' => __('Download leads', 'cocomarshmallow')],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_position' => 23,
        'menu_icon'    => 'dashicons-email-alt',
        'supports'     => ['title'],
        'capability_type' => 'post',
        'capabilities' => ['create_posts' => 'do_not_allow'],
        'map_meta_cap' => true,
    ]);
});

add_filter('manage_cm_lead_posts_columns', static fn ($c) => ['cb' => $c['cb'], 'title' => __('Name', 'cocomarshmallow'), 'cm_email' => __('Email', 'cocomarshmallow'),
    'cm_file' => __('Downloaded', 'cocomarshmallow'), 'cm_mailing' => __('Mailing list', 'cocomarshmallow'), 'date' => $c['date']]);
add_action('manage_cm_lead_posts_custom_column', static function ($col, $id) {
    if ($col === 'cm_email') {
        $e = get_post_meta($id, 'lead_email', true);
        echo $e ? '<a href="mailto:' . esc_attr($e) . '">' . esc_html($e) . '</a>' : '';
    } elseif ($col === 'cm_file') {
        echo esc_html(get_post_meta($id, 'lead_download', true));
    } elseif ($col === 'cm_mailing') {
        echo get_post_meta($id, 'lead_consent', true) ? esc_html(get_post_meta($id, 'lead_mailing_status', true) ?: __('Opted in', 'cocomarshmallow')) : '—';
    }
}, 10, 2);

/** Has this visitor already given their details? */
function cm_has_download_access(): bool
{
    return !empty($_COOKIE[CM_DL_COOKIE]);
}

function cm_activity_file_url(int $activity_id): string
{
    $file_id = (int) cm_field('activity_file', $activity_id);
    return $file_id ? (string) wp_get_attachment_url($file_id) : '';
}

/** The "Join our mailing list" button, or '' until a link has been set in Site settings. */
function cm_mailing_list_button(string $class = 'button button--outline'): string
{
    $url = trim((string) cm_field('mailing_list_url', 'option'));
    if ($url === '') {
        return '';
    }
    $label = trim((string) cm_field('mailing_list_label', 'option')) ?: __('Join our mailing list', 'cocomarshmallow');
    return '<a class="' . esc_attr($class) . '" href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($label)
        . '<span class="screen-reader-text"> ' . esc_html__('(opens in a new tab)', 'cocomarshmallow') . '</span></a>';
}

function cm_emailoctopus_subscribe(string $name, string $email): string
{
    $list = trim((string) cm_field('emailoctopus_list_id', 'option'));
    if ($list === '' || !defined('EMAILOCTOPUS_API_KEY')) {
        return 'Opted in (not synced)';
    }
    $parts = preg_split('/\s+/', trim($name), 2);
    $resp  = wp_remote_request('https://api.emailoctopus.com/lists/' . rawurlencode($list) . '/contacts', [
        'method'  => 'PUT',
        'timeout' => 10,
        'headers' => ['Authorization' => 'Bearer ' . EMAILOCTOPUS_API_KEY, 'Content-Type' => 'application/json'],
        'body'    => wp_json_encode([
            'email_address' => $email,
            'fields'        => array_filter(['FirstName' => $parts[0] ?? '', 'LastName' => $parts[1] ?? '']),
            'status'        => 'subscribed',
        ]),
    ]);
    if (is_wp_error($resp)) {
        return 'Sync failed: ' . $resp->get_error_message();
    }
    $code = (int) wp_remote_retrieve_response_code($resp);
    return $code >= 200 && $code < 300 ? 'Subscribed' : 'Sync failed (HTTP ' . $code . ')';
}

function cm_handle_download(): void
{
    $back = remove_query_arg(['cm_dl_error', 'cm_got', 'get'], wp_get_referer() ?: home_url('/childrens-activities/'));
    $id   = absint($_POST['cm_activity'] ?? 0);
    $fail = static function (string $why) use ($back, $id) {
        wp_safe_redirect(add_query_arg(['get' => $id, 'cm_dl_error' => $why], $back) . '#download-form');
        exit;
    };

    if (!isset($_POST['cm_dl_nonce']) || !wp_verify_nonce($_POST['cm_dl_nonce'], 'cm_download')) {
        $fail('nonce');
    }
    if (!empty($_POST['cm_website'])) { // honeypot
        wp_safe_redirect($back);
        exit;
    }
    $name  = sanitize_text_field(wp_unslash($_POST['cm_dl_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['cm_dl_email'] ?? ''));
    $join  = !empty($_POST['cm_dl_join']);
    $post  = get_post($id);
    if (!$post || $post->post_type !== 'cm_activity' || cm_activity_file_url($id) === '') {
        wp_safe_redirect($back);
        exit;
    }
    if ($name === '' || !is_email($email)) {
        $fail('invalid');
    }

    $lead = wp_insert_post([
        'post_type' => 'cm_lead', 'post_status' => 'publish', 'post_title' => $name,
        'meta_input' => ['lead_email' => $email, 'lead_download' => $post->post_title, 'lead_consent' => $join ? 1 : 0],
    ]);
    if ($lead && $join) {
        update_post_meta($lead, 'lead_mailing_status', cm_emailoctopus_subscribe($name, $email));
    }

    setcookie(CM_DL_COOKIE, '1', ['expires' => time() + 90 * DAY_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax']);
    $_COOKIE[CM_DL_COOKIE] = '1';
    wp_safe_redirect(add_query_arg('cm_got', $id, $back) . '#download-ready');
    exit;
}
add_action('admin_post_nopriv_cm_download', 'cm_handle_download');
add_action('admin_post_cm_download', 'cm_handle_download');
