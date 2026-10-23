<?php
defined('ABSPATH') || exit;
if (!function_exists('WC') || !WC()->cart) {
    return;
}
$count = WC()->cart->get_cart_contents_count();
?>
<a class="cart-link" href="<?php echo esc_url(wc_get_cart_url()); ?>">
    <?php echo cm_icon('cart'); ?>
    <span class="screen-reader-text"><?php echo esc_html(sprintf(_n('Basket, %d item', 'Basket, %d items', $count, 'cocomarshmallow'), $count)); ?></span>
    <span class="cart-link__count" aria-hidden="true"><?php echo esc_html($count); ?></span>
</a>
