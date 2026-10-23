<?php
defined('ABSPATH') || exit;
get_header(); ?>
<main id="main" tabindex="-1">
    <div class="container container--narrow section text-center">
        <img class="round-img" src="<?php echo esc_url(cm_asset('img/coco.jpg')); ?>" width="180" height="180" alt="">
        <h1 class="page-hero__title"><?php esc_html_e('Oh no, this page has wandered off', 'cocomarshmallow'); ?></h1>
        <p><?php esc_html_e('Coco could not find what you were looking for. Try searching, or head back home.', 'cocomarshmallow'); ?></p>
        <?php get_search_form(); ?>
        <p><a class="button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back to home', 'cocomarshmallow'); ?></a></p>
    </div>
</main>
<?php get_footer();
