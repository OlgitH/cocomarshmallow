<?php
/** Homepage: full-bleed hero (latest book) + featured books. */
defined('ABSPATH') || exit;
get_header();

$hero = cm_hero_product();
?>
<main id="main" tabindex="-1">

    <section class="hero" aria-labelledby="hero-heading">
        <div class="container container--wide hero__inner">
            <div class="hero__text">
                <h1 id="hero-heading" class="hero__kicker"><?php esc_html_e('Children’s author in Bath', 'cocomarshmallow'); ?></h1>
                <?php if ($hero) :
                    $hid   = $hero->get_id();
                    $blurb = cm_field('book_blurb', $hid) ?: wp_strip_all_tags($hero->get_short_description());
                    ?>
                    <p class="hero__label"><?php esc_html_e('Latest book', 'cocomarshmallow'); ?></p>
                    <h2 class="hero__title"><?php echo esc_html($hero->get_name()); ?></h2>
                    <?php if ($blurb) : ?><p class="hero__blurb"><?php echo wp_kses_post(nl2br(esc_html($blurb))); ?></p><?php endif; ?>
                    <p class="hero__actions">
                        <a class="button button--light" href="<?php echo esc_url(cm_buy_now_url($hero)); ?>">
                            <?php esc_html_e('Buy now', 'cocomarshmallow'); ?><span class="screen-reader-text">: <?php echo esc_html($hero->get_name()); ?></span>
                        </a>
                        <a class="button button--ghost" href="<?php echo esc_url($hero->get_permalink()); ?>">
                            <?php esc_html_e('Read more', 'cocomarshmallow'); ?><span class="screen-reader-text">: <?php echo esc_html($hero->get_name()); ?></span>
                        </a>
                    </p>
                <?php else : ?>
                    <p class="hero__label"><?php esc_html_e('Latest book', 'cocomarshmallow'); ?></p>
                    <h2 class="hero__title">Happiness</h2>
                    <p class="hero__blurb"><?php esc_html_e('Join Coco Marshmallow on a gentle adventure to discover what happiness feels like, with a fun mindfulness activity inside.', 'cocomarshmallow'); ?></p>
                    <p class="hero__actions"><a class="button button--light" href="<?php echo esc_url(home_url('/books/')); ?>"><?php esc_html_e('Buy now', 'cocomarshmallow'); ?></a></p>
                <?php endif; ?>
            </div>

            <div class="hero__media">
                <?php if ($hero && $hero->get_image_id()) : ?>
                    <?php echo wp_get_attachment_image($hero->get_image_id(), 'cm-book', false, [
                        'class' => 'hero__cover', 'loading' => 'eager', 'fetchpriority' => 'high',
                        'alt' => sprintf(__('Cover of %s', 'cocomarshmallow'), $hero->get_name()),
                    ]); ?>
                <?php else : ?>
                    <img class="hero__cover" src="<?php echo esc_url(cm_asset('img/happiness-cover.jpg')); ?>" width="720" height="726" fetchpriority="high"
                         alt="<?php esc_attr_e('Cover of Happiness: Coco Marshmallow the cat balancing on one leg among colourful bushes', 'cocomarshmallow'); ?>">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="section section--white" aria-labelledby="featured-heading">
        <div class="container container--wide">
            <header class="section__header">
                <h2 id="featured-heading" class="section__title"><?php esc_html_e('Featured books', 'cocomarshmallow'); ?></h2>
                <a class="text-link" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/books/')); ?>"><?php esc_html_e('See all books', 'cocomarshmallow'); ?><span aria-hidden="true"> →</span></a>
            </header>
            <?php
            $featured = function_exists('wc_get_products') ? wc_get_products(['limit' => 4, 'status' => 'publish', 'featured' => true, 'orderby' => 'date', 'order' => 'DESC']) : [];
            if (!$featured && function_exists('wc_get_products')) {
                $featured = wc_get_products(['limit' => 4, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC']);
            }
            ?>
            <?php if ($featured) : ?>
                <div class="book-grid">
                    <?php foreach ($featured as $product) { get_template_part('template-parts/book-card', null, ['product' => $product]); } ?>
                </div>
            <?php else : ?>
                <p><?php esc_html_e('New books are on their way. Check back soon!', 'cocomarshmallow'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php
    $reviews = new WP_Query(['post_type' => 'cm_review', 'posts_per_page' => 9, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'], 'no_found_rows' => true]);
    $source = ''; if ($reviews->have_posts()) : ?>
    <section class="section section--reviews" aria-labelledby="reviews-heading">
        <div class="container container--wide">
            <header class="section__header">
                <h2 id="reviews-heading" class="section__title"><?php esc_html_e('What our readers say', 'cocomarshmallow'); ?></h2>
                <?php
                $ratings = array_filter(array_map(static fn ($r) => (float) (cm_field('review_rating', $r->ID) ?: 5), $reviews->posts));
                $avg     = $ratings ? array_sum($ratings) / count($ratings) : 0;
                if ($avg) : ?>
                    <p class="reviews__summary">
                        <?php echo cm_stars($avg); ?>
                        <span><?php echo esc_html(sprintf(_n('%1$s out of 5 from %2$d review', '%1$s out of 5 from %2$d reviews', count($ratings), 'cocomarshmallow'), number_format($avg, 1), count($ratings))); ?></span>
                    </p>
                <?php endif; ?>
            </header>
            <ul class="review-grid" role="list">
                <?php while ($reviews->have_posts()) : $reviews->the_post();
                    $reply    = cm_field('review_reply', get_the_ID());
                    $reply_by = cm_field('review_reply_by', get_the_ID()) ?: 'Lysha and Amy';
                    $source   = cm_field("review_source", get_the_ID()) ?: $source;
                    ?>
                    <li class="review">
                        <figure class="review__card">
                            <?php echo cm_stars(cm_field('review_rating', get_the_ID()) ?: 5, 'review__stars'); ?>
                            <p class="review__badge"><?php echo cm_icon('heart'); ?><span><?php esc_html_e('Recommends', 'cocomarshmallow'); ?></span></p>
                            <blockquote class="review__text"><?php echo wp_kses_post(wpautop(get_the_content())); ?></blockquote>
                            <figcaption class="review__by">
                                <span class="review__name"><?php the_title(); ?></span>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                            </figcaption>
                            <?php if ($reply) : ?>
                                <div class="review__reply">
                                    <p class="review__reply-by"><?php echo esc_html(sprintf(__('Reply from %s', 'cocomarshmallow'), $reply_by)); ?></p>
                                    <p><?php echo esc_html($reply); ?></p>
                                </div>
                            <?php endif; ?>
                        </figure>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
            <?php if ($source) : ?><p class="reviews__source"><?php echo esc_html(sprintf(__('Reviews from our %s shop.', 'cocomarshmallow'), $source)); ?></p><?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

</main>
<?php get_footer();
