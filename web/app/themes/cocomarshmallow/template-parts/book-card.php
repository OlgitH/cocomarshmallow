<?php
/** A single book card. Expects to run inside a WooCommerce product loop context via $args['product']. */
defined('ABSPATH') || exit;
/** @var WC_Product $product */
$product = $args['product'];
$id      = $product->get_id();
$author  = cm_field('book_author', $id);
$age     = cm_field('book_age_range', $id);
?>
<article class="book-card">
    <a class="book-card__image" href="<?php echo esc_url($product->get_permalink()); ?>" tabindex="-1" aria-hidden="true">
        <?php echo $product->get_image('cm-card', ['loading' => 'lazy', 'alt' => '']); ?>
    </a>
    <div class="book-card__body">
        <h3 class="book-card__title"><a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
        <?php if ($author) : ?><p class="book-card__meta"><?php echo esc_html('By ' . $author); ?></p><?php endif; ?>
        <?php if ($age) : ?><p class="book-card__meta"><?php echo esc_html('Ages ' . preg_replace('/^\s*ages?\s*/i', '', $age)); ?></p><?php endif; ?>
        <p class="book-card__price"><?php echo wp_kses_post($product->get_price_html()); ?></p>
        <a class="button button--small" href="<?php echo esc_url($product->get_permalink()); ?>">
            <?php esc_html_e('View book', 'cocomarshmallow'); ?><span class="screen-reader-text">: <?php echo esc_html($product->get_name()); ?></span>
        </a>
    </div>
</article>
