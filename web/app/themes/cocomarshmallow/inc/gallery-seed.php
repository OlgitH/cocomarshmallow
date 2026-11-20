<?php
defined('ABSPATH') || exit;

/**
 * One-off: add the first three children's artworks to the Art gallery (only when the gallery is empty).
 * Pictures come from the theme and are copied into the media library; edit or delete them in the dashboard afterwards.
 * Add the child's name and age on each artwork when known.
 */
add_action('init', static function () {
    if (get_option('cm_gallery_seed_v1') || !post_type_exists('cm_artwork')) {
        return;
    }
    update_option('cm_gallery_seed_v1', 1); // set first so a failed upload can never loop
    if (get_posts(['post_type' => 'cm_artwork', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids'])) {
        return;
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $items = [
        ['cat-jumping-frame.webp', 'Coco jumping for joy', 'Coco Marshmallow jumping for joy, coloured in crayon, in a wooden frame'],
        ['mouse-oval-frame.webp', 'Mouse in a rainbow dress', 'A mouse in a rainbow dungaree dress, coloured in crayon, in a gold oval frame'],
        ['cat-oval-frame.webp', 'Coco with arms wide', 'Coco Marshmallow with arms wide open, coloured in pencil, in a gold oval frame'],
    ];
    foreach ($items as $order => [$file, $title, $alt]) {
        $src = get_theme_file_path('assets/img/gallery/' . $file);
        if (!file_exists($src)) {
            continue;
        }
        $post_id = wp_insert_post(['post_type' => 'cm_artwork', 'post_status' => 'publish', 'post_title' => $title, 'menu_order' => $order]);
        if (!$post_id || is_wp_error($post_id)) {
            continue;
        }
        $att = media_handle_sideload(['name' => $file, 'tmp_name' => (function ($s) { $t = wp_tempnam($s); copy($s, $t); return $t; })($src)], $post_id, $title);
        if (!is_wp_error($att)) {
            update_post_meta($att, '_wp_attachment_image_alt', $alt);
            set_post_thumbnail($post_id, $att);
        }
    }
}, 40);
