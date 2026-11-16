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
        <?php $mb = cm_mailing_list_button(); if ($mb) : ?><p class="downloads-join"><?php esc_html_e('Want to hear about new activities and books?', 'cocomarshmallow'); ?> <?php echo $mb; ?></p><?php endif; ?>
        <?php
        $get_id = absint($_GET['get'] ?? 0);
        $got_id = absint($_GET['cm_got'] ?? 0);
        $dl_err = sanitize_key($_GET['cm_dl_error'] ?? '');
        $valid  = static fn (int $i) => $i && get_post_type($i) === 'cm_activity' && cm_activity_file_url($i) !== '';
        if ($got_id && cm_has_download_access() && $valid($got_id)) : ?>
            <div id="download-ready" class="download-panel download-panel--ready" role="status" tabindex="-1">
                <h3><?php esc_html_e('Thank you! Your download is ready.', 'cocomarshmallow'); ?></h3>
                <p>
                    <a class="button" href="<?php echo esc_url(cm_activity_file_url($got_id)); ?>" download>
                        <?php echo cm_icon('download'); ?><span><?php echo esc_html(sprintf(__('Download %s', 'cocomarshmallow'), get_the_title($got_id))); ?></span>
                    </a>
                    <?php echo cm_mailing_list_button(); ?>
                </p>
            </div>
        <?php elseif ($get_id && !cm_has_download_access() && $valid($get_id)) : ?>
            <form id="download-form" class="form download-panel" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
                <h3><?php echo esc_html(sprintf(__('Get “%s”', 'cocomarshmallow'), get_the_title($get_id))); ?></h3>
                <p><?php esc_html_e('Pop in your name and email and your download will be ready straight away.', 'cocomarshmallow'); ?></p>
                <?php if ($dl_err) : ?>
                    <p class="notice notice--error" role="alert"><?php echo esc_html($dl_err === 'invalid' ? __('Please enter your name and a valid email address.', 'cocomarshmallow') : __('Sorry, something went wrong. Please try again.', 'cocomarshmallow')); ?></p>
                <?php endif; ?>
                <input type="hidden" name="action" value="cm_download">
                <input type="hidden" name="cm_activity" value="<?php echo esc_attr($get_id); ?>">
                <?php wp_nonce_field('cm_download', 'cm_dl_nonce'); ?>
                <div class="form__row">
                    <label for="cm_dl_name"><?php esc_html_e('Your name', 'cocomarshmallow'); ?> <span class="req" aria-hidden="true">*</span></label>
                    <input type="text" id="cm_dl_name" name="cm_dl_name" autocomplete="name" required aria-required="true">
                </div>
                <div class="form__row">
                    <label for="cm_dl_email"><?php esc_html_e('Email address', 'cocomarshmallow'); ?> <span class="req" aria-hidden="true">*</span></label>
                    <input type="email" id="cm_dl_email" name="cm_dl_email" autocomplete="email" required aria-required="true">
                </div>
                <div class="form__row form__row--check">
                    <label><input type="checkbox" name="cm_dl_join" value="1"> <?php esc_html_e('Yes, please add me to the mailing list for news about new books and activities.', 'cocomarshmallow'); ?></label>
                </div>
                <div class="form__hp" aria-hidden="true">
                    <label for="cm_website_dl">Leave this field empty</label>
                    <input type="text" id="cm_website_dl" name="cm_website" tabindex="-1" autocomplete="off">
                </div>
                <p class="form__note"><?php
                    $privacy = get_privacy_policy_url();
                    echo $privacy
                        ? wp_kses_post(sprintf(__('We’ll only use your details to send you this download, and to email you news if you tick the box. See our <a href="%s">privacy policy</a>.', 'cocomarshmallow'), esc_url($privacy)))
                        : esc_html__('We’ll only use your details to send you this download, and to email you news if you tick the box.', 'cocomarshmallow'); ?></p>
                <button class="button" type="submit"><?php esc_html_e('Get my download', 'cocomarshmallow'); ?></button>
                <a class="button button--outline" href="<?php echo esc_url(get_permalink($act_page_id)); ?>"><?php esc_html_e('Cancel', 'cocomarshmallow'); ?></a>
            </form>
        <?php endif; ?>
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
                                        <?php if ($url) :
                                            $href = cm_has_download_access() ? $url : add_query_arg('get', get_the_ID(), get_permalink($act_page_id)) . '#download-form'; ?>
                                            <a class="button" href="<?php echo esc_url($href); ?>"<?php echo cm_has_download_access() ? ' download' : ''; ?>>
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
