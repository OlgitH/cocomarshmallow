<?php
/** Wrapper for all WooCommerce templates (shop = "Books", product, cart, checkout, account). */
defined('ABSPATH') || exit;
get_header();
?>
<main id="main" tabindex="-1">
    <header class="page-hero">
        <div class="container container--wide">
            <?php cm_breadcrumbs(); ?>
            <?php $tag = is_product() ? 'p' : 'h1'; // the product template supplies its own H1 ?>
            <<?php echo $tag; ?> class="page-hero__title">
                <?php
                if (is_shop()) {
                    esc_html_e('Books', 'cocomarshmallow');
                } elseif (is_product()) {
                    esc_html_e('Book details', 'cocomarshmallow');
                } elseif (is_product_taxonomy()) {
                    single_term_title();
                } else {
                    the_title();
                }
                ?>
            </<?php echo $tag; ?>>
        </div>
    </header>
    <div class="section">
        <div class="container container--wide shop-wrap">
            <?php woocommerce_content(); ?>
        </div>
    </div>
</main>
<?php get_footer();
