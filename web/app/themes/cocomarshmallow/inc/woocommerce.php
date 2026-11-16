<?php
defined('ABSPATH') || exit;

add_action('after_setup_theme', static function () {
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 480,
        'single_image_width'    => 720,
        'product_grid'          => ['default_columns' => 3, 'min_columns' => 1, 'max_columns' => 4],
    ]);
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});

// We ship our own layout CSS for WooCommerce; drop the blocks/general layout stylesheet clash on classic templates.
add_filter('woocommerce_enqueue_styles', static function ($styles) {
    unset($styles['woocommerce-general']);
    return $styles;
});

// Page title is output once by woocommerce.php, so drop WooCommerce's duplicate H1.
add_filter('woocommerce_show_page_title', '__return_false');

// Wrap WooCommerce templates in our markup.
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

add_filter('loop_shop_columns', static fn () => 3);
add_filter('loop_shop_per_page', static fn () => 12);

/** Cart count in header; refreshes via WooCommerce fragments. */
add_filter('woocommerce_add_to_cart_fragments', static function ($fragments) {
    ob_start();
    get_template_part('template-parts/cart-link');
    $fragments['a.cart-link'] = ob_get_clean();
    return $fragments;
});

/** The book shown in the homepage hero: flagged book, else the newest. */
function cm_hero_product(): ?WC_Product
{
    if (!function_exists('wc_get_products')) {
        return null;
    }
    $flagged = wc_get_products(['limit' => 1, 'status' => 'publish', 'meta_key' => 'show_in_hero', 'meta_value' => '1', 'orderby' => 'date', 'order' => 'DESC']);
    if ($flagged) {
        return $flagged[0];
    }
    $latest = wc_get_products(['limit' => 1, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC']);
    return $latest ? $latest[0] : null;
}

/** "Buy now": add to cart and go straight to checkout for simple products. */
function cm_buy_now_url(WC_Product $product): string
{
    if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
        return add_query_arg('add-to-cart', $product->get_id(), wc_get_checkout_url());
    }
    return $product->get_permalink();
}

/**
 * The shop page lives at /shop/ (it used to be /books/). Self-heals on any load:
 * finds the shop page (falling back to the old /books/ page), frees the "shop" slug
 * if another page holds it, and renames the page.
 */
add_action('init', static function () {
    if (!function_exists('wc_get_page_id')) {
        return;
    }
    $id = (int) wc_get_page_id('shop');
    if ($id <= 0) {
        $old = get_page_by_path('books');
        if (!$old) {
            return;
        }
        $id = (int) $old->ID;
        update_option('woocommerce_shop_page_id', $id);
    }
    if (get_post_field('post_name', $id) === 'shop') {
        return;
    }
    foreach (get_posts(['post_type' => 'page', 'name' => 'shop', 'post_status' => 'any', 'posts_per_page' => -1, 'exclude' => [$id]]) as $other) {
        wp_update_post(['ID' => $other->ID, 'post_name' => 'shop-old-' . $other->ID]);
    }
    wp_update_post(['ID' => $id, 'post_name' => 'shop', 'post_title' => 'Shop', 'post_status' => 'publish']);
    flush_rewrite_rules();
}, 20);

/** Old /books/ links (including custom menu items) go to the shop page. */
add_filter('wp_nav_menu_objects', static function ($items) {
    if (!function_exists('wc_get_page_permalink')) {
        return $items;
    }
    foreach ($items as $item) {
        if ($item->type !== 'custom') {
            continue;
        }
        $path = untrailingslashit((string) wp_parse_url($item->url, PHP_URL_PATH));
        parse_str((string) wp_parse_url($item->url, PHP_URL_QUERY), $q);
        if ($path === '/books' || (int) ($q['page_id'] ?? 0) === (int) wc_get_page_id('shop')) {
            $item->url = wc_get_page_permalink('shop');
        }
    }
    return $items;
});

add_action('template_redirect', static function () {
    if (!function_exists('wc_get_page_permalink')) {
        return;
    }
    $path   = untrailingslashit((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    $target = wc_get_page_permalink('shop');
    if ($path === '/books' && untrailingslashit((string) wp_parse_url($target, PHP_URL_PATH)) !== '/books') {
        wp_safe_redirect($target, 301);
        exit;
    }
});
