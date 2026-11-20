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

/** Whether a book is flagged "Coming soon" (no price, no Add to basket). */
function cm_is_coming_soon($product): bool
{
    $id = $product instanceof WC_Product ? $product->get_id() : (int) $product;
    return (string) get_post_meta($id, 'coming_soon', true) === '1';
}

/** Books in the homepage carousel: the "Latest book" first, then the rest newest first. Falls back to the newest book. */
function cm_carousel_products(): array
{
    if (!function_exists('wc_get_products')) {
        return [];
    }
    $books = wc_get_products(['limit' => 12, 'status' => 'publish', 'meta_key' => 'show_in_hero', 'meta_value' => '1', 'orderby' => 'date', 'order' => 'DESC']);
    if (!$books) {
        $books = wc_get_products(['limit' => 1, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC']);
    }
    usort($books, static fn ($a, $b) => (int) (get_post_meta($b->get_id(), 'is_latest_book', true) === '1') <=> (int) (get_post_meta($a->get_id(), 'is_latest_book', true) === '1'));
    return $books;
}

/** The first carousel book (kept for anything that wants a single hero book). */
function cm_hero_product(): ?WC_Product
{
    $books = cm_carousel_products();
    return $books ? $books[0] : null;
}

/** Only one book can be the latest: ticking it on one clears it from the others. */
add_action('save_post_product', static function ($post_id) {
    if (wp_is_post_revision($post_id) || get_post_meta($post_id, 'is_latest_book', true) !== '1') {
        return;
    }
    foreach (get_posts(['post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'exclude' => [$post_id], 'meta_key' => 'is_latest_book', 'meta_value' => '1']) as $other) {
        update_post_meta($other, 'is_latest_book', '');
    }
}, 99);

/** Coming soon: not purchasable, and "Coming soon" replaces the price. */
add_filter('woocommerce_is_purchasable', static fn ($ok, $product) => cm_is_coming_soon($product) ? false : $ok, 10, 2);
add_filter('woocommerce_get_price_html', static fn ($html, $product) => cm_is_coming_soon($product) ? '<span class="coming-soon">' . esc_html__('Coming soon', 'cocomarshmallow') . '</span>' : $html, 10, 2);

/** Books without a cover yet show a "Cover coming soon" picture. */
add_filter('woocommerce_placeholder_img_src', static fn () => cm_asset('img/cover-coming-soon.svg'));

/**
 * One-off: add the Bravery book (coming soon) and make sure the existing books are in the carousel,
 * with the newest existing book marked as the latest. Never runs twice, so later edits in the dashboard stick.
 */
add_action('init', static function () {
    if (get_option('cm_bravery_v1') || !class_exists('WC_Product_Simple')) {
        return;
    }
    update_option('cm_bravery_v1', 1);
    $existing = wc_get_products(['limit' => -1, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC']);
    foreach ($existing as $i => $book) {
        if (get_post_meta($book->get_id(), 'show_in_hero', true) === '') {
            update_post_meta($book->get_id(), 'show_in_hero', '1');
        }
    }
    if ($existing && !get_posts(['post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'is_latest_book', 'meta_value' => '1'])) {
        update_post_meta($existing[0]->get_id(), 'is_latest_book', '1');
    }
    foreach ($existing as $book) {
        if (strcasecmp($book->get_name(), 'Bravery') === 0) {
            return; // already added by hand
        }
    }
    $blurb = 'In this second book, we meet new friends who help Coco Marshmallow learn that being brave doesn’t mean you never feel afraid, but that being brave means trying even when you do. Discover another mindfulness activity and colouring sheets to enjoy!';
    $p = new WC_Product_Simple();
    $p->set_name('Bravery');
    $p->set_slug('bravery');
    $p->set_status('publish');
    $p->set_catalog_visibility('visible');
    $p->set_short_description($blurb);
    $p->set_description($blurb);
    $id = $p->save();
    if ($id) {
        update_post_meta($id, 'book_blurb', $blurb);
        update_post_meta($id, 'show_in_hero', '1');
        update_post_meta($id, 'coming_soon', '1');
    }
}, 35);

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
