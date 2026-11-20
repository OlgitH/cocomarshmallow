<?php
defined('ABSPATH') || exit;

/**
 * Postage and delivery page: created once (never overwritten, so edits in the dashboard stick),
 * linked from the footer, plus a "we post on Monday and Thursday" notice before purchase.
 */
add_action('init', static function () {
    if (get_option('cm_postage_page_v1') || get_page_by_path('postage-and-delivery')) {
        return;
    }
    $content = "<h2>UK delivery</h2>\n<p>We post orders on Monday and Thursday each week. All orders are sent via Royal Mail standard 2nd class. If you would like your book to be posted 1st class, please be sure to choose this option at check out. The estimated shipping time for 2nd class within the UK is 3-5 working days but this is a guide and not guaranteed. It is the customer's responsibility to order in good time. Please ensure your delivery address is correct as we can not take responsibility for undelivered items where the delivery address has been supplied incorrectly.</p>\n<h2>Christmas orders</h2>\n<p>To ensure delivery before Christmas, please place your order before <strong>Friday 18th December</strong>.</p>";
    if (wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Postage and delivery', 'post_name' => 'postage-and-delivery', 'post_content' => $content])) {
        update_option('cm_postage_page_v1', 1);
    }
}, 30);

function cm_postage_url(): string
{
    $page = get_page_by_path('postage-and-delivery');
    return $page && $page->post_status === 'publish' ? get_permalink($page) : '';
}

function cm_postage_notice(): void
{
    if (function_exists('is_product') && is_product() && cm_is_coming_soon(get_the_ID())) {
        return;
    }
    $url = cm_postage_url();
    echo '<p class="cm-post-days">' . esc_html__('We post out on a Monday and Thursday each week.', 'cocomarshmallow');
    if ($url) {
        echo ' <a href="' . esc_url($url) . '">' . esc_html__('Postage and delivery', 'cocomarshmallow') . '</a>';
    }
    echo '</p>';
}
add_action('woocommerce_single_product_summary', 'cm_postage_notice', 35);
add_action('woocommerce_before_cart', 'cm_postage_notice');
add_action('woocommerce_before_checkout_form', 'cm_postage_notice', 5);
