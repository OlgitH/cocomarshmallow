<?php
defined('ABSPATH') || exit;

/** Get a field via ACF when active, otherwise fall back to plain post meta / option. */
function cm_field(string $name, $post_id = null)
{
    if (function_exists('get_field')) {
        return get_field($name, $post_id);
    }
    if ($post_id === 'option') {
        return get_option("options_{$name}", '');
    }
    return get_post_meta($post_id ?: get_the_ID(), $name, true);
}

function cm_asset(string $path): string
{
    return get_theme_file_uri('assets/' . ltrim($path, '/'));
}

/** Inline SVG icons (decorative; always paired with visible or screen-reader text). */
function cm_icon(string $name): string
{
    $paths = [
        'instagram' => '<path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7zm5 3.5a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9zm0 2a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM17.2 5.8a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>',
        'facebook'  => '<path d="M13.5 22v-8h2.7l.5-3.3h-3.2V8.6c0-.9.4-1.7 1.8-1.7h1.5V4.1S15.1 3.9 14 3.9c-2.6 0-4.2 1.6-4.2 4.4v2.4H7v3.3h2.8v8h3.7z"/>',
        'tiktok'    => '<path d="M16.6 2h-3.2v13.2a2.8 2.8 0 1 1-2.8-2.8c.3 0 .5 0 .8.1V9.2a6 6 0 1 0 5.2 5.9V8.6a7.4 7.4 0 0 0 4.3 1.4V6.8A4.3 4.3 0 0 1 16.6 2z"/>',
        'x'         => '<path d="M17.8 3h3L14.2 10.5 22 21h-6.1l-4.8-6.3L5.6 21h-3l7.1-8L2.2 3h6.2l4.3 5.7L17.8 3zm-1 16.2h1.7L7.5 4.7H5.7l11.1 14.5z"/>',
        'youtube'   => '<path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8zM10 15V9l5.2 3L10 15z"/>',
        'download'  => '<path d="M12 3v11.2l3.6-3.6 1.4 1.4-6 6-6-6 1.4-1.4 3.6 3.6V3h2zM4 19h16v2H4v-2z"/>',
        'heart'     => '<path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.8 5 6.1 5c1.9 0 3.2 1 3.9 2.2h.0C10.7 6 12 5 13.9 5c3.3 0 5.2 3.4 3.7 6.8C19.5 16.4 12 21 12 21z"/>',
        'cart'      => '<path d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM1 2h3.3l.7 3H21l-2.2 9H7.2L4 4H1V2zm5.1 5 1.6 5h9.5l1.2-5H6.1z"/>',
    ];
    if (!isset($paths[$name])) {
        return '';
    }
    return '<svg class="icon icon--' . esc_attr($name) . '" aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">' . $paths[$name] . '</svg>';
}

/** Social links from the Site Settings options page. */
function cm_social_links(): array
{
    $links = [];
    foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'x' => 'X (Twitter)', 'youtube' => 'YouTube'] as $key => $label) {
        $url = cm_field("social_{$key}", 'option');
        if ($url) {
            $links[] = ['key' => $key, 'label' => $label, 'url' => $url];
        }
    }
    return $links;
}

function cm_breadcrumbs(): void
{
    if (function_exists('rank_math_the_breadcrumbs')) {
        rank_math_the_breadcrumbs();
    }
}

/** Human readable "PDF, 1.2 MB" for an attachment ID. */
function cm_file_meta(int $attachment_id): string
{
    $path = get_attached_file($attachment_id);
    $ext  = strtoupper(pathinfo((string) $path, PATHINFO_EXTENSION));
    $size = ($path && file_exists($path)) ? size_format(filesize($path), 1) : '';
    return trim($ext . ($size ? ', ' . $size : ''), ', ');
}

/** Visual star rating, announced to screen readers as text (stars themselves are hidden). */
function cm_stars($rating, string $class = ''): string
{
    $rating = max(0, min(5, (float) $rating));
    $star   = '<path d="M12 2.5l2.9 6.1 6.6.9-4.8 4.6 1.2 6.6L12 17.5l-5.9 3.2 1.2-6.6L2.5 9.5l6.6-.9L12 2.5z"/>';
    $out    = '';
    for ($i = 1; $i <= 5; $i++) {
        $state = $rating >= $i ? 'is-full' : 'is-empty';
        $out  .= '<svg class="star ' . $state . '" aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="22" height="22">' . $star . '</svg>';
    }
    return sprintf(
        '<span class="stars %s" role="img" aria-label="%s">%s</span>',
        esc_attr($class),
        esc_attr(sprintf(__('Rated %s out of 5', 'cocomarshmallow'), rtrim(rtrim(number_format($rating, 1), '0'), '.'))),
        $out
    );
}
