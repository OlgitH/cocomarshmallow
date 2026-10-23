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
        <div class="container container--narrow section">
            <div class="entry__content prose"><?php the_content(); ?></div>
        </div>
    </article>
</main>
<?php endwhile;
get_footer();
