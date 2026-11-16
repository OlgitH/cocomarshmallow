<?php
defined('ABSPATH') || exit;

/**
 * One-off: turn the "Children's activities" tab in the primary menu into "Resources",
 * with Children's activities, Children's art gallery and Resources for nurseries beneath it.
 * Runs once (flag option), so later edits in Appearance > Menus are never overwritten.
 */
add_action('init', static function () {
    if (get_option('cm_resources_menu_v1') || !function_exists('wp_update_nav_menu_item')) {
        return;
    }
    $menu_id = (int) (get_nav_menu_locations()['primary'] ?? 0);
    if (!$menu_id) {
        return; // no menu assigned: the theme's fallback menu already has the new layout
    }
    $acts = get_posts(['post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'page-childrens-activities.php', 'posts_per_page' => 1, 'post_status' => 'publish']);
    if (!$acts) {
        return;
    }
    $acts_page = $acts[0];

    // Find the existing top-level activities tab.
    $parent = null;
    foreach (wp_get_nav_menu_items($menu_id) as $item) {
        $path = untrailingslashit((string) wp_parse_url($item->url, PHP_URL_PATH));
        parse_str((string) wp_parse_url($item->url, PHP_URL_QUERY), $q);
        if (!(int) $item->menu_item_parent && ((int) ($q['page_id'] ?? 0) === $acts_page->ID || $path === untrailingslashit((string) wp_parse_url(get_permalink($acts_page), PHP_URL_PATH)))) {
            $parent = $item;
            break;
        }
    }
    if (!$parent) {
        return;
    }

    // The Art gallery page: find it, or create it so the menu link works.
    $gal = get_posts(['post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'page-art-gallery.php', 'posts_per_page' => 1, 'post_status' => 'any']);
    $gal_id = $gal ? $gal[0]->ID : (int) wp_insert_post([
        'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Art gallery', 'post_name' => 'art-gallery',
        'post_content' => 'Wonderful artwork from our young readers. Have you drawn Coco Marshmallow? Send us your picture and it could appear here!',
        'meta_input' => ['_wp_page_template' => 'page-art-gallery.php'],
    ]);
    $nur = get_page_by_path('resources-for-nurseries');

    // Rename the tab (keeps its link to the activities page).
    wp_update_nav_menu_item($menu_id, $parent->db_id, [
        'menu-item-title' => 'Resources', 'menu-item-url' => $parent->url, 'menu-item-type' => 'custom',
        'menu-item-status' => 'publish', 'menu-item-position' => $parent->menu_order,
    ]);

    $children = [
        ['Children’s activities', get_permalink($acts_page)],
        ['Children’s art gallery', $gal_id ? get_permalink($gal_id) : ''],
        ['Resources for nurseries', $nur ? get_permalink($nur) : ''],
    ];
    $pos = 1;
    foreach ($children as [$title, $url]) {
        if ($url === '') {
            continue;
        }
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-type' => 'custom',
            'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent->db_id, 'menu-item-position' => $pos++,
        ]);
    }
    update_option('cm_resources_menu_v1', 1);
}, 30);
