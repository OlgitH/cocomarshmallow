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
    <header class="page-hero page-hero--pink">
        <div class="container container--wide page-hero__split">
            <div>
                <?php cm_breadcrumbs(); ?>
                <h1 class="page-hero__title"><?php the_title(); ?></h1>
                <div class="page-hero__lead prose"><?php echo trim((string) cm_field('act_intro')) !== '' ? wp_kses_post(apply_filters('the_content', cm_field('act_intro'))) : apply_filters('the_content', get_the_content()); ?></div>
            </div>
            <?php $act_img = (int) cm_field('act_image'); ?>
            <?php if ($act_img) : ?>
                <?php echo wp_get_attachment_image($act_img, 'large', false, ['class' => 'page-hero__art', 'loading' => 'eager']); ?>
            <?php else : ?>
            <img class="page-hero__art" src="<?php echo esc_url(cm_asset('img/meadow-scene.jpg')); ?>" width="480" height="456" loading="eager" alt="<?php esc_attr_e('An illustrated meadow with a squirrel, a hare, a mouse holding acorns and birds in the sunshine', 'cocomarshmallow'); ?>">
            <?php endif; ?>
        </div>
    </header>
    <?php $act_page_id = get_the_ID(); endwhile; ?>

    <section class="section container container--wide" aria-labelledby="downloads-heading">
        <h2 id="downloads-heading" class="section__title"><?php echo esc_html(trim((string) cm_field('act_heading', $act_page_id)) ?: 'Free downloads'); ?></h2>
        <?php
        $activities = new WP_Query(['post_type' => 'cm_activity', 'posts_per_page' => -1, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
        // Group by activity type: "Colouring sheets" gets its own tab, everything else is under "Activities".
        $groups = ['activities' => [], 'colouring-sheets' => []];
        foreach ($activities->posts as $a) {
            $groups[has_term('colouring-sheets', 'cm_activity_type', $a) ? 'colouring-sheets' : 'activities'][] = $a;
        }
        $tabs = [
            'activities'       => __('Activities', 'cocomarshmallow'),
            'colouring-sheets' => __('Colouring sheets', 'cocomarshmallow'),
        ];
        $tabs = array_filter($tabs, static fn ($k) => !empty($groups[$k]), ARRAY_FILTER_USE_KEY);
        if ($tabs) : ?>
            <div class="tabs" data-tabs>
                <?php if (count($tabs) > 1) : ?>
                    <ul class="tabs__list" role="tablist" aria-label="<?php esc_attr_e('Types of download', 'cocomarshmallow'); ?>" hidden>
                        <?php foreach ($tabs as $slug => $label) : ?>
                            <li role="presentation"><button type="button" role="tab" id="tab-<?php echo esc_attr($slug); ?>" aria-controls="panel-<?php echo esc_attr($slug); ?>" aria-selected="false" tabindex="-1"><?php echo esc_html($label); ?></button></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php foreach ($tabs as $slug => $label) : ?>
                    <div class="tabs__panel" id="panel-<?php echo esc_attr($slug); ?>" role="tabpanel" aria-labelledby="tab-<?php echo esc_attr($slug); ?>">
                        <?php if (count($tabs) > 1) : ?><h3 class="tabs__heading"><?php echo esc_html($label); ?></h3><?php endif; ?>
                        <ul class="activity-grid" role="list">
                            <?php foreach ($groups[$slug] as $post) : setup_postdata($post);
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
                            <?php endforeach; wp_reset_postdata(); ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p><?php echo esc_html(trim((string) cm_field('act_empty', $act_page_id)) ?: 'Activities are coming soon. Check back shortly!'); ?></p>
        <?php endif; ?>
    </section>
    <?php cm_render_sections($act_page_id); ?>
</main>
<?php get_footer();
