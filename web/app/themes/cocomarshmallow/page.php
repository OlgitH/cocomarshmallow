<?php
defined('ABSPATH') || exit;
get_header();
while (have_posts()) : the_post(); ?>
<main id="main" tabindex="-1">
    <article <?php post_class('entry'); ?>>
        <header class="page-hero">
            <div class="container container--narrow">
                <?php cm_breadcrumbs(); ?>
                <h1 class="page-hero__title"><?php the_title(); ?></h1>
            </div>
        </header>
        <?php
        $follow = is_page('about')
            ? array_filter(cm_social_links(), static fn ($s) => in_array($s['key'], ['instagram', 'facebook'], true))
            : [];
        ?>
        <div class="container container--wide section<?php echo $follow ? ' has-aside' : ''; ?>">
            <div class="entry__content prose"><?php the_content(); ?></div>
            <?php if ($follow) : ?>
                <aside class="follow-us" aria-labelledby="follow-us-heading">
                    <h2 id="follow-us-heading" class="follow-us__title"><?php esc_html_e('Follow us on socials', 'cocomarshmallow'); ?></h2>
                    <ul class="social" role="list">
                        <?php foreach ($follow as $s) : ?>
                            <li>
                                <a href="<?php echo esc_url($s['url']); ?>" rel="noopener me" target="_blank">
                                    <?php echo cm_icon($s['key']); ?>
                                    <span class="screen-reader-text"><?php echo esc_html($s['label']); ?> <?php esc_html_e('(opens in a new tab)', 'cocomarshmallow'); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
            <?php endif; ?>
        </div>
    </article>
</main>
<?php endwhile;
get_footer();
