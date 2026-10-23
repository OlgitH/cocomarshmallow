<?php
/**
 * Template Name: Children's activities
 * Lists downloadable activities (cm_activity) with accessible download buttons.
 */
defined('ABSPATH') || exit;
get_header();
?>
<main id="main" tabindex="-1">
    <?php while (have_posts()) : the_post(); ?>
    <header class="page-hero page-hero--green">
        <div class="container container--wide page-hero__split">
            <div>
                <?php cm_breadcrumbs(); ?>
                <h1 class="page-hero__title"><?php the_title(); ?></h1>
                <div class="page-hero__lead prose"><?php the_content(); ?></div>
            </div>
            <img class="page-hero__art" src="<?php echo esc_url(cm_asset('img/meadow-scene.jpg')); ?>" width="480" height="456" loading="eager" alt="<?php esc_attr_e('An illustrated meadow with a squirrel, a hare, a mouse holding acorns and birds in the sunshine', 'cocomarshmallow'); ?>">
        </div>
    </header>
    <?php endwhile; ?>

    <section class="section container container--wide" aria-labelledby="downloads-heading">
        <h2 id="downloads-heading" class="section__title"><?php esc_html_e('Free downloads', 'cocomarshmallow'); ?></h2>
        <?php
        $activities = new WP_Query(['post_type' => 'cm_activity', 'posts_per_page' => -1, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
        if ($activities->have_posts()) : ?>
            <ul class="activity-grid" role="list">
                <?php while ($activities->have_posts()) : $activities->the_post();
                    $file_id = (int) cm_field('activity_file', get_the_ID());
                    $url     = $file_id ? wp_get_attachment_url($file_id) : '';
                    $age     = cm_field('activity_age_range', get_the_ID());
                    $time    = cm_field('activity_duration', get_the_ID());
                    ?>
                    <li class="activity-card">
                        <div class="activity-card__image">
                            <?php if (has_post_thumbnail()) {
                                the_post_thumbnail('cm-card', ['alt' => '', 'loading' => 'lazy']);
                            } else { ?>
                                <img src="<?php echo esc_url(cm_asset('img/coco.jpg')); ?>" alt="" width="480" height="480" loading="lazy">
                            <?php } ?>
                        </div>
                        <div class="activity-card__body">
                            <h3 class="activity-card__title"><?php the_title(); ?></h3>
                            <?php if ($age || $time) : ?>
                                <p class="activity-card__meta">
                                    <?php echo esc_html(implode(' · ', array_filter([$age ? 'Ages ' . preg_replace('/^\s*ages?\s*/i', '', $age) : '', $time]))); ?>
                                </p>
                            <?php endif; ?>
                            <div class="activity-card__text"><?php echo wp_kses_post(get_the_excerpt() ?: wp_trim_words(get_the_content(), 30)); ?></div>
                            <?php if ($url) : ?>
                                <a class="button" href="<?php echo esc_url($url); ?>" download>
                                    <?php echo cm_icon('download'); ?>
                                    <span><?php esc_html_e('Download', 'cocomarshmallow'); ?><span class="screen-reader-text"> <?php the_title(); ?> (<?php echo esc_html(cm_file_meta($file_id)); ?>)</span></span>
                                </a>
                                <span class="activity-card__file" aria-hidden="true"><?php echo esc_html(cm_file_meta($file_id)); ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        <?php else : ?>
            <p><?php esc_html_e('Activities are coming soon. Check back shortly!', 'cocomarshmallow'); ?></p>
        <?php endif; ?>
    </section>
</main>
<?php get_footer();
