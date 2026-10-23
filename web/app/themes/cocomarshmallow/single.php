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
                <p class="entry__meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
            </div>
        </header>
        <div class="container container--narrow section">
            <?php if (has_post_thumbnail()) : ?><figure class="entry__figure"><?php the_post_thumbnail('large'); ?></figure><?php endif; ?>
            <div class="entry__content prose"><?php the_content(); ?></div>
            <nav class="post-nav" aria-label="<?php esc_attr_e('Post navigation', 'cocomarshmallow'); ?>">
                <?php previous_post_link('%link', '← %title'); ?>
                <?php next_post_link('%link', '%title →'); ?>
            </nav>
        </div>
    </article>
</main>
<?php endwhile;
get_footer();
