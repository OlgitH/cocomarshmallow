<?php
/**
 * Template Name: Resources for nurseries & preschools
 * Editable content comes from the page editor (headings, text, lists);
 * this template adds the hero, a book call-to-action and a download call-to-action.
 */
defined('ABSPATH') || exit;
get_header();
$book = cm_hero_product();
?>
<main id="main" tabindex="-1">
    <?php while (have_posts()) : the_post(); ?>
    <header class="page-hero page-hero--green">
        <div class="container container--wide page-hero__split">
            <div>
                <?php cm_breadcrumbs(); ?>
                <h1 class="page-hero__title"><?php the_title(); ?></h1>
                <?php if (has_excerpt()) : ?><p class="page-hero__lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
                <p class="hero__actions">
                    <a class="button" href="<?php echo esc_url(home_url('/childrens-activities/')); ?>"><?php esc_html_e('Free printable activities', 'cocomarshmallow'); ?></a>
                    <a class="button button--outline" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Ask about your setting', 'cocomarshmallow'); ?></a>
                </p>
            </div>
            <img class="page-hero__art" src="<?php echo esc_url(cm_asset('img/coco.jpg')); ?>" width="480" height="480" alt="<?php esc_attr_e('Coco Marshmallow, a friendly cat in a grey cardigan, smiling among colourful leaves', 'cocomarshmallow'); ?>">
        </div>
    </header>

    <div class="container container--narrow section">
        <div class="entry__content prose prose--sections"><?php the_content(); ?></div>
    </div>
    <?php endwhile; ?>

    <section class="cta-band" aria-labelledby="cta-heading">
        <div class="container container--wide cta-band__inner">
            <?php if ($book) : ?>
                <div class="cta-band__media">
                    <?php echo $book->get_image('cm-card', ['alt' => sprintf(__('Cover of %s', 'cocomarshmallow'), $book->get_name()), 'loading' => 'lazy']); ?>
                </div>
            <?php endif; ?>
            <div class="cta-band__text">
                <h2 id="cta-heading"><?php esc_html_e('Bring Coco to your nursery or preschool', 'cocomarshmallow'); ?></h2>
                <p><?php esc_html_e('Read a story together, then try the mindfulness activity. Download our free resources or take a look at the books.', 'cocomarshmallow'); ?></p>
                <p class="hero__actions">
                    <?php if ($book) : ?><a class="button" href="<?php echo esc_url($book->get_permalink()); ?>"><?php echo esc_html(sprintf(__('See %s', 'cocomarshmallow'), $book->get_name())); ?></a><?php endif; ?>
                    <a class="button button--outline" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/books/')); ?>"><?php esc_html_e('All books', 'cocomarshmallow'); ?></a>
                </p>
            </div>
        </div>
    </section>
</main>
<?php get_footer();
