<?php
/** Blog / news & events listing, archives and search results. */
defined('ABSPATH') || exit;
get_header();
?>
<main id="main" tabindex="-1">
    <header class="page-hero">
        <div class="container">
            <?php cm_breadcrumbs(); ?>
            <h1 class="page-hero__title">
                <?php
                if (is_search()) {
                    printf(esc_html__('Search results for “%s”', 'cocomarshmallow'), esc_html(get_search_query()));
                } elseif (is_home() && get_option('page_for_posts')) {
                    echo esc_html(get_the_title(get_option('page_for_posts')));
                } elseif (is_archive()) {
                    the_archive_title();
                } else {
                    esc_html_e('News & events', 'cocomarshmallow');
                }
                ?>
            </h1>
        </div>
    </header>

    <div class="container section">
        <?php if (have_posts()) : ?>
            <div class="post-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class('post-card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <a class="post-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail('cm-card', ['alt' => '']); ?></a>
                        <?php endif; ?>
                        <div class="post-card__body">
                            <p class="post-card__meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
                            <h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <div class="post-card__excerpt"><?php the_excerpt(); ?></div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(['mid_size' => 1, 'prev_text' => __('Previous', 'cocomarshmallow'), 'next_text' => __('Next', 'cocomarshmallow'), 'screen_reader_text' => __('Posts navigation', 'cocomarshmallow')]); ?>
        <?php else : ?>
            <p><?php esc_html_e('Nothing here yet. Please check back soon.', 'cocomarshmallow'); ?></p>
            <?php get_search_form(); ?>
        <?php endif; ?>
    </div>
</main>
<?php get_footer();
