<?php
/**
 * Template Name: Children's art gallery
 * Shows artwork (cm_artwork) sent in by children, with an accessible enlarge dialog.
 */
defined('ABSPATH') || exit;
get_header();
?>
<main id="main" tabindex="-1">
    <?php while (have_posts()) : the_post(); ?>
    <header class="page-hero page-hero--pink">
        <div class="container container--wide">
            <?php cm_breadcrumbs(); ?>
            <h1 class="page-hero__title"><?php the_title(); ?></h1>
            <div class="page-hero__lead prose"><?php echo trim((string) cm_field('gallery_intro')) !== '' ? wp_kses_post(apply_filters('the_content', cm_field('gallery_intro'))) : apply_filters('the_content', get_the_content()); ?></div>
        </div>
    </header>
    <?php $gal_page_id = get_the_ID(); endwhile; ?>

    <nav class="subnav container container--wide" aria-label="<?php esc_attr_e('Children’s activities sections', 'cocomarshmallow'); ?>">
        <ul>
            <li><a href="<?php echo esc_url(home_url('/childrens-activities/')); ?>"><?php esc_html_e('Downloads', 'cocomarshmallow'); ?></a></li>
            <li><a href="<?php echo esc_url(get_permalink($gal_page_id)); ?>" aria-current="page"><?php esc_html_e('Art gallery', 'cocomarshmallow'); ?></a></li>
        </ul>
    </nav>

    <section class="section container container--wide" aria-label="<?php esc_attr_e('Artwork', 'cocomarshmallow'); ?>">
        <?php
        $art = new WP_Query(['post_type' => 'cm_artwork', 'posts_per_page' => -1, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
        if ($art->have_posts()) : ?>
            <ul class="art-grid" role="list">
                <?php while ($art->have_posts()) : $art->the_post();
                    $tid = get_post_thumbnail_id();
                    if (!$tid) { continue; }
                    $artist = trim((string) cm_field('artwork_artist', get_the_ID()));
                    $age    = trim((string) cm_field('artwork_age', get_the_ID()));
                    $by     = trim(($artist ? sprintf(__('By %s', 'cocomarshmallow'), $artist) : '') . ($age ? ($artist ? ', ' : '') . sprintf(__('age %s', 'cocomarshmallow'), preg_replace('/^\s*age\s*/i', '', $age)) : ''));
                    $alt    = trim((string) get_post_meta($tid, '_wp_attachment_image_alt', true)) ?: sprintf(__('Artwork: %s', 'cocomarshmallow'), get_the_title());
                    ?>
                    <li class="art-card">
                        <a class="art-card__link" href="<?php echo esc_url(wp_get_attachment_image_url($tid, 'full')); ?>" data-art-full="<?php echo esc_url(wp_get_attachment_image_url($tid, 'large')); ?>" data-art-alt="<?php echo esc_attr($alt); ?>">
                            <?php echo wp_get_attachment_image($tid, 'cm-card', false, ['alt' => $alt, 'loading' => 'lazy']); ?>
                            <span class="screen-reader-text"><?php esc_html_e('View larger', 'cocomarshmallow'); ?></span>
                        </a>
                        <div class="art-card__body">
                            <h2 class="art-card__title"><?php the_title(); ?></h2>
                            <?php if ($by) : ?><p class="art-card__meta"><?php echo esc_html($by); ?></p><?php endif; ?>
                            <?php if (has_excerpt()) : ?><p class="art-card__text"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
                        </div>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
            <dialog class="art-dialog" aria-label="<?php esc_attr_e('Artwork', 'cocomarshmallow'); ?>">
                <img src="" alt="">
                <form method="dialog"><button class="button" type="submit"><?php esc_html_e('Close', 'cocomarshmallow'); ?></button></form>
            </dialog>
        <?php else : ?>
            <p><?php echo esc_html(trim((string) cm_field('gallery_empty', $gal_page_id)) ?: 'Artwork is coming soon. Check back shortly!'); ?></p>
        <?php endif; ?>
    </section>
    <?php cm_render_sections($gal_page_id); ?>
</main>
<?php get_footer();
