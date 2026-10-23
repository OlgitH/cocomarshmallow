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
